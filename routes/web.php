Route::get('/_diag-7f3a', function () {
    try {
        return response()->json([
            'users_columns' => \Illuminate\Support\Facades\Schema::getColumnListing('users'),
            'migrations' => \Illuminate\Support\Facades\DB::table('migrations')->pluck('migration'),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => class_basename($e),
            'message' => \Illuminate\Support\Str::limit($e->getMessage(), 300),
        ], 500);
    }
});