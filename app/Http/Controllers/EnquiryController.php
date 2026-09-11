<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnquiryRequest;
use App\Http\Requests\UpdateEnquiryStatusRequest;
use App\Models\TourEnquiry;
use App\Services\EnquiryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function __construct(private readonly EnquiryService $enquiryService) {}

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        return response()->json($this->enquiryService->paginate((int) ($data['page'] ?? 1)));
    }

    public function store(StoreEnquiryRequest $request): JsonResponse
    {
        return response()->json($this->enquiryService->create($request->validated()), 201);
    }

    public function updateStatus(
        UpdateEnquiryStatusRequest $request,
        TourEnquiry $enquiry,
    ): JsonResponse {
        return response()->json($this->enquiryService->updateStatus(
            $enquiry,
            $request->validated('status'),
        ));
    }
}
