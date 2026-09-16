<?php

namespace Tests\Feature\Admin\Kyc;

use App\Enums\ExpertKycStatus;
use App\Models\Admin;
use App\Models\Expert;
use App\Models\ExpertKycApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminKycTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('kyc');
    }

    public function test_only_an_administrator_can_access_admin_kyc_routes(): void
    {
        $this->getJson('/api/admin/kyc/applications')->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create(), ['user:access']);
        $this->getJson('/api/admin/kyc/applications')->assertForbidden();

        Sanctum::actingAs(Expert::factory()->verified()->create(), ['expert:access']);
        $this->getJson('/api/admin/kyc/applications')->assertForbidden();
    }

    public function test_admin_list_supports_search_status_filter_and_pagination_without_paths(): void
    {
        $first = $this->submittedApplication('Legal Expert', 'legal');
        $this->submittedApplication('Finance Expert', 'finance');

        Sanctum::actingAs(Admin::factory()->create(), [Admin::ACCESS_ABILITY]);
        $response = $this->getJson('/api/admin/kyc/applications?search=Legal&status=submitted&perPage=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data.applications')
            ->assertJsonPath('data.applications.0.id', $first->id)
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonMissingPath('data.applications.0.path');
    }

    public function test_admin_can_review_all_documents_and_approve_a_complete_application(): void
    {
        $application = $this->submittedApplication();
        $admin = Admin::factory()->create();
        Sanctum::actingAs($admin, [Admin::ACCESS_ABILITY]);

        $this->postJson("/api/admin/kyc/applications/{$application->id}/start-review")
            ->assertOk()
            ->assertJsonPath('data.application.status', 'under_review');

        foreach ($application->documents as $document) {
            $this->putJson("/api/admin/kyc/applications/{$application->id}/documents/{$document->id}/review", [
                'reviewed' => true,
            ])->assertOk();
        }

        $this->postJson("/api/admin/kyc/applications/{$application->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.application.status', 'verified');

        $this->assertSame(ExpertKycStatus::Approved, $application->expert->refresh()->kyc_status);
        $this->assertDatabaseHas('expert_kyc_applications', [
            'id' => $application->id,
            'reviewed_by_admin_id' => $admin->id,
            'status' => 'verified',
        ]);
        $this->assertDatabaseCount('expert_kyc_status_histories', 4);
    }

    public function test_admin_cannot_approve_before_every_document_is_reviewed(): void
    {
        $application = $this->submittedApplication();
        Sanctum::actingAs(Admin::factory()->create(), [Admin::ACCESS_ABILITY]);
        $this->postJson("/api/admin/kyc/applications/{$application->id}/start-review")->assertOk();

        $this->postJson("/api/admin/kyc/applications/{$application->id}/approve")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('documents');
    }

    public function test_reject_and_request_information_require_a_reason(): void
    {
        $application = $this->submittedApplication();
        Sanctum::actingAs(Admin::factory()->create(), [Admin::ACCESS_ABILITY]);
        $this->postJson("/api/admin/kyc/applications/{$application->id}/start-review")->assertOk();

        $this->postJson("/api/admin/kyc/applications/{$application->id}/reject", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reason');

        $this->postJson("/api/admin/kyc/applications/{$application->id}/request-information", [
            'reason' => 'Please upload a clearer identity document.',
        ])->assertOk()->assertJsonPath('data.application.status', 'needs_information');
    }

    public function test_repeated_or_conflicting_admin_decisions_are_rejected(): void
    {
        $application = $this->submittedApplication();
        Sanctum::actingAs(Admin::factory()->create(), [Admin::ACCESS_ABILITY]);
        $this->postJson("/api/admin/kyc/applications/{$application->id}/start-review")->assertOk();
        $this->postJson("/api/admin/kyc/applications/{$application->id}/reject", [
            'reason' => 'The submitted identity evidence is invalid.',
        ])->assertOk();

        $this->postJson("/api/admin/kyc/applications/{$application->id}/approve")
            ->assertStatus(409);
    }

    public function test_admin_cannot_review_a_document_from_a_different_application(): void
    {
        $first = $this->submittedApplication('First Expert');
        $second = $this->submittedApplication('Second Expert');
        Sanctum::actingAs(Admin::factory()->create(), [Admin::ACCESS_ABILITY]);
        $this->postJson("/api/admin/kyc/applications/{$first->id}/start-review")->assertOk();

        $this->putJson("/api/admin/kyc/applications/{$first->id}/documents/{$second->documents->first()->id}/review", [
            'reviewed' => true,
        ])->assertNotFound();
    }

    public function test_draft_application_is_not_visible_to_admin(): void
    {
        $application = ExpertKycApplication::factory()->create();
        Sanctum::actingAs(Admin::factory()->create(), [Admin::ACCESS_ABILITY]);

        $this->getJson("/api/admin/kyc/applications/{$application->id}")->assertNotFound();
    }

    private function submittedApplication(string $name = 'Ahmad Ali', string $domain = 'legal'): ExpertKycApplication
    {
        $expert = Expert::factory()->verified()->create(['name' => $name, 'domain' => $domain]);
        Sanctum::actingAs($expert, [Expert::ACCESS_ABILITY]);

        $this->putJson('/api/expert/kyc', [
            'fullName' => $name,
            'country' => 'JO',
            'language' => 'ar',
            'domain' => $domain,
            'jurisdiction' => 'Jordan',
            'experiences' => [[
                'jobTitle' => 'Senior Consultant',
                'organization' => 'Example Office',
                'current' => true,
            ]],
            'qualifications' => [],
            'credentials' => [],
        ])->assertOk();

        foreach (['identity', 'cv'] as $type) {
            $this->post('/api/expert/kyc/documents', [
                'documentType' => $type,
                'file' => UploadedFile::fake()->create($type.'.pdf', 100, 'application/pdf'),
            ], ['Accept' => 'application/json'])->assertCreated();
        }

        $this->postJson('/api/expert/kyc/submit')->assertOk();

        return ExpertKycApplication::query()
            ->where('expert_id', $expert->id)
            ->with(['expert', 'documents'])
            ->firstOrFail();
    }
}
