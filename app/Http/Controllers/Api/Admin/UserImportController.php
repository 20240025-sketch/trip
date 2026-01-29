<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserImportController extends Controller
{
    /**
     * Download CSV template for user import
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="user_import_template.csv"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, ['name', 'email', 'password', 'role', 'class', 'number', 'room_day1', 'room_day2', 'room_day3', 'bus_number']);
            
            // Example rows
            fputcsv($file, ['山田太郎', 'yamada@example.com', 'password123', 'user', '1年A組', '12', '101', '201', '301', '1号車']);
            fputcsv($file, ['管理者', 'admin@example.com', 'admin123', 'admin', '', '', '', '', '', '']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import users from CSV
     */
    public function import(Request $request): JsonResponse
    {
        // Check if user is admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => '管理者のみがユーザーをインポートできます。'
            ], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        
        $data = array_map(function($line) {
            return str_getcsv($line);
        }, file($path));

        // Remove header row
        $header = array_shift($data);
        
        $imported = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            // Minimum required fields: name, email, password
            if (count($row) < 3) {
                $errors[] = "行 " . ($index + 2) . ": 不完全なデータ（最低3列必要: name, email, password）";
                continue;
            }

            // Build user data with all available fields
            $userData = [
                'name' => trim($row[0]),
                'email' => trim($row[1]),
                'password' => trim($row[2]),
                'role' => isset($row[3]) && trim($row[3]) !== '' ? trim($row[3]) : 'user',
            ];

            // Optional fields
            $optionalData = [
                'class' => isset($row[4]) && trim($row[4]) !== '' ? trim($row[4]) : null,
                'number' => isset($row[5]) && trim($row[5]) !== '' ? trim($row[5]) : null,
                'room_day1' => isset($row[6]) && trim($row[6]) !== '' ? trim($row[6]) : null,
                'room_day2' => isset($row[7]) && trim($row[7]) !== '' ? trim($row[7]) : null,
                'room_day3' => isset($row[8]) && trim($row[8]) !== '' ? trim($row[8]) : null,
                'bus_number' => isset($row[9]) && trim($row[9]) !== '' ? trim($row[9]) : null,
            ];

            $validator = Validator::make($userData, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'role' => 'required|in:user,admin',
            ]);

            if ($validator->fails()) {
                $errors[] = "行 " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                continue;
            }

            try {
                User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make($userData['password']),
                    'role' => $userData['role'],
                    'class' => $optionalData['class'],
                    'number' => $optionalData['number'],
                    'room_day1' => $optionalData['room_day1'],
                    'room_day2' => $optionalData['room_day2'],
                    'room_day3' => $optionalData['room_day3'],
                    'bus_number' => $optionalData['bus_number'],
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "行 " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return response()->json([
            'message' => "{$imported}件のユーザーをインポートしました。",
            'imported' => $imported,
            'errors' => $errors,
        ]);
    }

    /**
     * Download CSV template for assignment import
     */
    public function downloadAssignmentTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="assignment_import_template.csv"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, ['email', 'class', 'number', 'room_day1', 'room_day2', 'room_day3', 'bus_number']);
            
            // Example row
            fputcsv($file, ['yamada@example.com', '1-A', '10', '101', '202', '303', '1号車']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import assignments from CSV
     */
    public function importAssignments(Request $request): JsonResponse
    {
        // Check if user is admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => '管理者のみが部屋割・バス座席をインポートできます。'
            ], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        
        $data = array_map(function($line) {
            return str_getcsv($line);
        }, file($path));

        // Remove header row
        $header = array_shift($data);
        
        $updated = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            if (count($row) < 7) {
                $errors[] = "行 " . ($index + 2) . ": 不完全なデータ";
                continue;
            }

            $email = trim($row[0]);
            $user = User::where('email', $email)->first();

            if (!$user) {
                $errors[] = "行 " . ($index + 2) . ": ユーザーが見つかりません ({$email})";
                continue;
            }

            try {
                $user->update([
                    'class' => trim($row[1]) ?: null,
                    'number' => trim($row[2]) ?: null,
                    'room_day1' => trim($row[3]) ?: null,
                    'room_day2' => trim($row[4]) ?: null,
                    'room_day3' => trim($row[5]) ?: null,
                    'bus_number' => trim($row[6]) ?: null,
                ]);
                $updated++;
            } catch (\Exception $e) {
                $errors[] = "行 " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return response()->json([
            'message' => "{$updated}件のユーザー情報を更新しました。",
            'updated' => $updated,
            'errors' => $errors,
        ]);
    }
}
