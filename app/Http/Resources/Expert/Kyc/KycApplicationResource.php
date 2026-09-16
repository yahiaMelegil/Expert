<?php

namespace App\Http\Resources\Expert\Kyc;

use App\Enums\ExpertKycDocumentType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KycApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $identity = $this->documentOfType(ExpertKycDocumentType::Identity);
        $cv = $this->documentOfType(ExpertKycDocumentType::Cv);

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'attemptNumber' => $this->attempt_number,
            'status' => $this->status->value,
            'identityAndScope' => [
                'fullName' => $this->full_name,
                'email' => $this->email_snapshot,
                'country' => $this->country,
                'language' => $this->language,
                'domain' => $this->domain,
                'jurisdiction' => $this->jurisdiction,
                'identityEvidence' => $identity ? new KycDocumentResource($identity) : null,
            ],
            'experiences' => $this->experiences->map(fn ($item): array => [
                'id' => $item->id,
                'jobTitle' => $item->job_title,
                'organization' => $item->organization,
                'from' => $item->from_month,
                'to' => $item->to_month,
                'current' => $item->is_current,
                'description' => $item->description,
            ]),
            'qualifications' => $this->qualifications->map(fn ($item): array => [
                'id' => $item->id,
                'degree' => $item->degree,
                'field' => $item->field,
                'institution' => $item->institution,
                'graduationYear' => $item->graduation_year,
                'document' => $item->document ? new KycDocumentResource($item->document) : null,
            ]),
            'credentials' => $this->credentials->map(fn ($item): array => [
                'id' => $item->id,
                'type' => $item->type,
                'name' => $item->name,
                'issuer' => $item->issuer,
                'issueDate' => $item->issue_date?->toDateString(),
                'expiryDate' => $item->expiry_date?->toDateString(),
                'document' => $item->document ? new KycDocumentResource($item->document) : null,
            ]),
            'cv' => $cv ? new KycDocumentResource($cv) : null,
            'workSamples' => KycDocumentResource::collection(
                $this->documents->where('document_type', ExpertKycDocumentType::WorkSample),
            ),
            'payoutReadiness' => $this->payout_readiness,
            'decisionReason' => $this->decision_reason,
            'submittedAt' => $this->submitted_at?->toISOString(),
            'reviewStartedAt' => $this->review_started_at?->toISOString(),
            'decidedAt' => $this->decided_at?->toISOString(),
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'canEdit' => $this->status->value === 'draft',
            'canResubmit' => $this->status->canStartNewAttempt(),
        ];
    }

    private function documentOfType(ExpertKycDocumentType $type)
    {
        return $this->documents->first(
            fn ($document): bool => $document->document_type === $type,
        );
    }
}
