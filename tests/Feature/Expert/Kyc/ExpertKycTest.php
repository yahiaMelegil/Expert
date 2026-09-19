<?php

namespace Tests\Feature\Expert\Kyc;

use App\Enums\ExpertKycApplicationStatus;
use App\Enums\ExpertKycDocumentType;
use App\Enums\ExpertKycStatus;
use App\Models\Admin;
use App\Models\Expert;
use App\Models\ExpertKycApplication;
use App\Models\ExpertKycDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExpertKycTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('kyc');
    }

    public function test_verified_expert_receives_prefilled_registration_data(): void
    {
        $expert = Expert::factory()->verified()->create([
            'name' => 'Ahmad Ali',
            'email' => 'ahmad@example.com',
            'country' => 'JO',
            'language' => 'ar',
            'domain' => 'legal',
        ]);
        $this->actingAsExpert($expert);

        $this->getJson('/api/expert/kyc')
            ->assertOk()
            ->assertJsonPath('data.kycStatus', 'not_submitted')
            ->assertJsonPath('data.prefill.fullName', 'Ahmad Ali')
            ->assertJsonPath('data.prefill.email', 'ahmad@example.com')
            ->assertJsonPath('data.prefill.country', 'JO')
            ->assertJsonPath('data.prefill.language', 'ar')
            ->assertJsonPath('data.prefill.domain', 'legal')
            ->assertJsonPath('data.application', null);
    }

    public function test_unverified_expert_and_other_account_types_cannot_access_expert_kyc(): void
    {
        $expert = Expert::factory()->create();
        $this->actingAsExpert($expert);
        $this->getJson('/api/expert/kyc')->assertForbidden();

        Sanctum::actingAs(User::factory()->create(), ['user:access']);
        $this->getJson('/api/expert/kyc')->assertForbidden();

        Sanctum::actingAs(Admin::factory()->create(), ['admin:access']);
        $this->getJson('/api/expert/kyc')->assertForbidden();
    }

    public function test_expert_can_save_a_draft_and_only_owns_the_created_application(): void
    {
        $expert = Expert::factory()->verified()->create([
            'country' => null,
            'language' => null,
            'domain' => null,
        ]);
        $this->actingAsExpert($expert);

        $this->putJson('/api/expert/kyc', $this->validDraft())
            ->assertOk()
            ->assertJsonPath('data.application.status', 'draft')
            ->assertJsonPath('data.application.identityAndScope.fullName', 'Ahmad Ali')
            ->assertJsonPath('data.application.experiences.0.jobTitle', 'Attorney');

        $application = ExpertKycApplication::query()->firstOrFail();
        $this->assertSame($expert->id, $application->expert_id);
        $this->assertSame(1, $application->attempt_number);
        $this->assertDatabaseCount('expert_kyc_status_histories', 1);
    }

    public function test_draft_update_is_rolled_back_when_a_child_record_is_not_owned(): void
    {
        $expert = Expert::factory()->verified()->create();
        $this->actingAsExpert($expert);
        $this->putJson('/api/expert/kyc', $this->validDraft())->assertOk();

        $this->putJson('/api/expert/kyc', [
            'fullName' => 'Name That Must Roll Back',
            'experiences' => [[
                'id' => 999999,
                'jobTitle' => 'Attorney',
                'organization' => 'Other Office',
            ]],
        ])->assertUnprocessable();

        $this->assertDatabaseHas('expert_kyc_applications', [
            'expert_id' => $expert->id,
            'full_name' => 'Ahmad Ali',
        ]);
    }

    public function test_incomplete_application_cannot_be_submitted(): void
    {
        $expert = Expert::factory()->verified()->create([
            'country' => null,
            'language' => null,
            'domain' => null,
        ]);
        $this->actingAsExpert($expert);
        $this->putJson('/api/expert/kyc', ['fullName' => 'Ahmad Ali'])->assertOk();

        $this->postJson('/api/expert/kyc/submit')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['country', 'language', 'domain', 'jurisdiction', 'identityEvidence', 'cv']);
    }

    public function test_expert_can_upload_replace_and_delete_private_documents(): void
    {
        $expert = Expert::factory()->verified()->create();
        $this->actingAsExpert($expert);
        $this->putJson('/api/expert/kyc', $this->validDraft())->assertOk();

        $first = $this->upload('identity', 'identity.pdf')->assertCreated();
        $firstDocument = ExpertKycDocument::query()->findOrFail($first->json('data.document.id'));
        Storage::disk('kyc')->assertExists($firstDocument->path);

        $second = $this->upload('identity', 'replacement.pdf')->assertCreated();
        $secondDocument = ExpertKycDocument::query()->findOrFail($second->json('data.document.id'));
        $this->assertDatabaseCount('expert_kyc_documents', 1);
        Storage::disk('kyc')->assertMissing($firstDocument->path);
        Storage::disk('kyc')->assertExists($secondDocument->path);
        $second->assertJsonMissingPath('data.document.path');

        $this->deleteJson('/api/expert/kyc/documents/'.$secondDocument->id)->assertOk();
        Storage::disk('kyc')->assertMissing($secondDocument->path);
    }

    public function test_invalid_document_is_rejected(): void
    {
        $expert = Expert::factory()->verified()->create();
        $this->actingAsExpert($expert);
        $this->putJson('/api/expert/kyc', $this->validDraft())->assertOk();

        $this->post('/api/expert/kyc/documents', [
            'documentType' => 'identity',
            'file' => UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream'),
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');

        $this->assertDatabaseCount('expert_kyc_documents', 0);
    }

    public function test_complete_application_can_be_submitted_and_then_cannot_be_edited(): void
    {
        $expert = Expert::factory()->verified()->create();
        $this->actingAsExpert($expert);
        $this->createCompleteDraft();

        $this->postJson('/api/expert/kyc/submit')
            ->assertOk()
            ->assertJsonPath('data.application.status', 'submitted');

        $this->assertSame(ExpertKycStatus::Pending, $expert->refresh()->kyc_status);
        $this->putJson('/api/expert/kyc', ['fullName' => 'Changed'])
            ->assertStatus(409);

        $document = ExpertKycDocument::query()->firstOrFail();
        $this->deleteJson('/api/expert/kyc/documents/'.$document->id)
            ->assertStatus(409);
    }

    public function test_expert_cannot_access_another_experts_document(): void
    {
        $owner = Expert::factory()->verified()->create();
        $this->actingAsExpert($owner);
        $this->putJson('/api/expert/kyc', $this->validDraft())->assertOk();
        $documentId = $this->upload('identity', 'identity.pdf')->json('data.document.id');

        $other = Expert::factory()->verified()->create();
        $this->actingAsExpert($other);
        $this->get('/api/expert/kyc/documents/'.$documentId, ['Accept' => 'application/json'])->assertNotFound();
        $this->deleteJson('/api/expert/kyc/documents/'.$documentId)->assertNotFound();
    }

    public function test_rejected_expert_can_start_a_new_attempt_without_mutating_history(): void
    {
        $expert = Expert::factory()->verified()->create();
        $this->actingAsExpert($expert);
        $this->createCompleteDraft();
        $this->postJson('/api/expert/kyc/submit')->assertOk();
        $application = ExpertKycApplication::query()->firstOrFail();

        $admin = Admin::factory()->create();
        $this->actingAsAdmin($admin);
        $this->postJson("/api/admin/kyc/applications/{$application->id}/start-review")->assertOk();
        $this->postJson("/api/admin/kyc/applications/{$application->id}/reject", [
            'reason' => 'Identity evidence is unclear.',
        ])->assertOk();

        $this->actingAsExpert($expert);
        $this->putJson('/api/expert/kyc', ['jurisdiction' => 'Jordan'])->assertOk()
            ->assertJsonPath('data.application.attemptNumber', 2)
            ->assertJsonPath('data.application.status', 'draft');

        $this->assertDatabaseHas('expert_kyc_applications', [
            'id' => $application->id,
            'status' => ExpertKycApplicationStatus::Rejected->value,
        ]);
        $this->assertDatabaseCount('expert_kyc_applications', 2);
    }

    public function test_replacing_a_document_in_a_retry_preserves_the_previous_attempt_file(): void
    {
        $expert = Expert::factory()->verified()->create();
        $this->actingAsExpert($expert);
        $this->createCompleteDraft();
        $this->postJson('/api/expert/kyc/submit')->assertOk();
        $firstAttempt = ExpertKycApplication::query()->with('documents')->firstOrFail();
        $identity = $firstAttempt->documents->first(
            fn (ExpertKycDocument $document): bool => $document->document_type === ExpertKycDocumentType::Identity,
        );

        $admin = Admin::factory()->create();
        $this->actingAsAdmin($admin);
        $this->postJson("/api/admin/kyc/applications/{$firstAttempt->id}/start-review")->assertOk();
        $this->postJson("/api/admin/kyc/applications/{$firstAttempt->id}/request-information", [
            'reason' => 'Upload a clearer identity image.',
            'requestedChanges' => [[
                'section' => 'identity_scope',
                'field' => 'identityEvidence',
                'documentId' => $identity->id,
                'message' => 'The identity image is not readable.',
            ]],
        ])->assertOk();

        $this->actingAsExpert($expert);
        $this->putJson('/api/expert/kyc', ['jurisdiction' => 'Jordan'])->assertOk();
        $this->upload('identity', 'clear-identity.pdf')->assertCreated();

        Storage::disk('kyc')->assertExists($identity->path);
        $this->assertDatabaseHas('expert_kyc_documents', [
            'application_id' => $firstAttempt->id,
            'path' => $identity->path,
        ]);
    }

    private function actingAsExpert(Expert $expert): void
    {
        Sanctum::actingAs($expert, [Expert::ACCESS_ABILITY]);
    }

    private function actingAsAdmin(Admin $admin): void
    {
        Sanctum::actingAs($admin, [Admin::ACCESS_ABILITY]);
    }

    private function validDraft(): array
    {
        return [
            'fullName' => 'Ahmad Ali',
            'country' => 'JO',
            'language' => 'ar',
            'domain' => 'legal',
            'jurisdiction' => 'Jordan',
            'experiences' => [[
                'jobTitle' => 'Attorney',
                'organization' => 'Example Law Firm',
                'from' => '2020-01',
                'current' => true,
                'description' => 'Commercial law experience.',
            ]],
            'qualifications' => [],
            'credentials' => [],
        ];
    }

    private function createCompleteDraft(): void
    {
        $this->putJson('/api/expert/kyc', $this->validDraft())->assertOk();
        $this->upload('identity', 'identity.pdf')->assertCreated();
        $this->upload('cv', 'cv.pdf')->assertCreated();
    }

    private function upload(string $type, string $name)
    {
        return $this->post('/api/expert/kyc/documents', [
            'documentType' => $type,
            'file' => UploadedFile::fake()->create($name, 100, 'application/pdf'),
        ], ['Accept' => 'application/json']);
    }
}
