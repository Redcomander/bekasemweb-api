<?php

namespace App\Http\Traits;

trait ApiResponse
{
    /**
     * Return a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return an error response.
     *
     * @param string $message
     * @param int $code
     * @param mixed $errors
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message = 'Error', int $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a created response.
     *
     * @param mixed $data
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createdResponse($data = null, string $message = 'Data berhasil dibuat')
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Return a not found response.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function notFoundResponse(string $message = 'Data tidak ditemukan')
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Return a validation error response.
     *
     * @param mixed $errors
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validationErrorResponse($errors, string $message = 'Validasi gagal')
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Return an unauthorized response.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthorizedResponse(string $message = 'Tidak memiliki akses')
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Return a forbidden response.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function forbiddenResponse(string $message = 'Akses ditolak')
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Return a paginated response.
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function paginatedResponse($paginator, string $message = 'Success')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ], 200);
    }
}
