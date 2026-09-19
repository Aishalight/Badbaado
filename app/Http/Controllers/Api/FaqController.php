<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFaqRequest;
use App\Http\Requests\Api\UpdateFaqRequest;
use App\Models\Faq;
use App\Services\AuditLogger;
use App\Support\AuditActions;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Faq::class);

        return response()->json(Faq::orderBy('sort_order')->orderBy('id')->get());
    }

    public function store(StoreFaqRequest $request): JsonResponse
    {
        $this->authorize('create', Faq::class);

        $faq = Faq::create([
            'question' => $request->validated('question'),
            'answer' => $request->validated('answer'),
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->auditLogger->record($request->user(), AuditActions::FAQ_CREATED, $faq, []);

        return response()->json(['message' => 'FAQ added.', 'id' => $faq->id], 201);
    }

    public function update(UpdateFaqRequest $request, Faq $faq): JsonResponse
    {
        $this->authorize('update', $faq);

        $faq->update($request->validated());

        $this->auditLogger->record($request->user(), AuditActions::FAQ_UPDATED, $faq, [
            'fields' => array_keys($request->validated()),
        ]);

        return response()->json(['message' => 'FAQ updated.']);
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $this->authorize('delete', $faq);

        $this->auditLogger->record(request()->user(), AuditActions::FAQ_DELETED, $faq, [
            'question' => $faq->question,
        ]);
        $faq->delete();

        return response()->json(['message' => 'FAQ deleted.']);
    }
}
