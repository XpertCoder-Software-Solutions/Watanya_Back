<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Grade;
use App\Models\Admin;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

/**
 * @OA\Tag(
 *     name="Admin Students",
 * )
 * @OA\Tag(
 *     name="Student Grades",
 * )
 */
class StudentsController
{
    /**
 * @OA\Get(
 *     path="/api/admin/students",
 *     operationId="getStudentsList",
 *     tags={"Admin Students"},
 *     summary="Get paginated list of students",
 *     description="Returns paginated list of students with optional filtering and search",
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Page number",
 *         required=false,
 *         @OA\Schema(type="integer", default=1)
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         description="Items per page",
 *         required=false,
 *         @OA\Schema(type="integer", default=10)
 *     ),
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         description="Search by student code",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="level",
 *         in="query",
 *         description="Filter by level",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="specialization",
 *         in="query",
 *         description="Filter by specialization",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="academic_year",
 *         in="query",
 *         description="Filter by academic year",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="current_page", type="integer", example=1),
 *             @OA\Property(property="per_page", type="integer", example=10),
 *             @OA\Property(property="total_pages", type="integer", example=5),
 *             @OA\Property(property="students", type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="code", type="string", example="ST2023001"),
 *                     @OA\Property(property="name", type="string", example="John Doe"),
 *                     @OA\Property(property="email", type="string", example="john@example.com"),
 *                     @OA\Property(property="phoneNumber", type="string", example="+1234567890"),
 *                     @OA\Property(property="level", type="string", example="One"),
 *                     @OA\Property(property="specialization", type="string", example="CS"),
 *                     @OA\Property(property="academic_year", type="string", example="2023-2024"),
 *                     @OA\Property(property="gpa", type="number", format="float", example=3.5)
 *                 )
 *             )
 *         )
 *     )
 * )
 */
public function index(Request $request)
{
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);

    $query = Student::query();

    // Search by student code
    if ($request->has('search')) {
        $query->where('code', 'like', '%' . $request->search . '%');
    }

    // Filter by level
    if ($request->has('level')) {
        $query->where('level', $request->level);
    }

    // Filter by specialization
    if ($request->has('specialization')) {
        $query->where('specialization', $request->specialization);
    }

    // Filter by academic year
    if ($request->has('academic_year')) {
        $query->where('academic_year', $request->academic_year);
    }

    $paginatedStudents = $query->select([
            'id',
            'code',
            'name',
            'email',
            'phoneNumber',
            'level',
            'specialization',
            'academic_year',
            'gpa'
        ])
        ->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
        'current_page' => $paginatedStudents->currentPage(),
        'per_page' => $paginatedStudents->perPage(),
        'total_pages' => $paginatedStudents->lastPage(),
        'students' => $paginatedStudents->items()
    ], 200);
}

    /**
     * @OA\Post(
     *     path="/api/admin/students",
     *     operationId="createStudent",
     *     tags={"Admin Students"},
     *     summary="Create a new student",
     *     description="Creates a new student record",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Student data",
     *         @OA\JsonContent(
     *             required={"code", "name", "email", "phoneNumber", "level", "specialization"},
     *             @OA\Property(property="code", type="string", example="ST2023001"),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="phoneNumber", type="string", example="+1234567890"),
     *             @OA\Property(property="level", type="string", enum={"One", "Two", "Three", "Four"}, example="One"),
     *             @OA\Property(property="specialization", type="string", enum={"CS", "IT"}, example="CS")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student added successfully"),
     *             @OA\Property(property="student", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="code", type="string", example="ST2023001"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="phoneNumber", type="string", example="+1234567890"),
     *                 @OA\Property(property="level", type="string", example="One"),
     *                 @OA\Property(property="specialization", type="string", example="CS"),
     *                 @OA\Property(property="Academic-Year", type="string", example="2023-2024"),
     *                 @OA\Property(property="gpa", type="number", format="float", example=0.0)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="field_name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The field_name field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:students,code',
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:students,email',
            'phoneNumber' => 'required|string|max:100',
            'level' => 'required|in:One,Two,Three,Four',
            'specialization' => 'required|in:CS,IT',
        ]);

        $currentAcademicYear = Admin::value('academic_year');

        $student = Student::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phoneNumber' => $validated['phoneNumber'],
            'level' => $validated['level'],
            'specialization' => $validated['specialization'],
            'password' => Hash::make($validated['code']),
            'academic_year' => $currentAcademicYear,
            'gpa' => 0.00,
        ]);

        $studentData = $student->makeHidden(['password', 'created_at', 'updated_at']);

        return response()->json([
            "student" => $studentData,
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/students/{id}",
     *     operationId="updateStudent",
     *     tags={"Admin Students"},
     *     summary="Update a student",
     *     description="Updates an existing student record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Student ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Student data to update",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="string", example="ST2023001"),
     *             @OA\Property(property="name", type="string", example="John Doe Updated"),
     *             @OA\Property(property="email", type="string", format="email", example="john.updated@example.com"),
     *             @OA\Property(property="phoneNumber", type="string", example="+9876543210"),
     *             @OA\Property(property="level", type="string", enum={"One", "Two", "Three", "Four"}, example="Two"),
     *             @OA\Property(property="specialization", type="string", enum={"CS", "IT"}, example="IT")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student updated successfully"),
     *             @OA\Property(property="student", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="code", type="string", example="ST2023001"),
     *                 @OA\Property(property="name", type="string", example="John Doe Updated"),
     *                 @OA\Property(property="email", type="string", example="john.updated@example.com"),
     *                 @OA\Property(property="phoneNumber", type="string", example="+9876543210"),
     *                 @OA\Property(property="level", type="string", example="Two"),
     *                 @OA\Property(property="specialization", type="string", example="IT"),
     *                 @OA\Property(property="academic_year", type="string", example="2023-2024"),
     *                 @OA\Property(property="gpa", type="number", format="float", example=0.0)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Student not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="field_name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The field_name field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            "code" => "sometimes|string|unique:students,code,{$id}",
            "name" => "sometimes|string|max:255",
            "email" => "sometimes|string|unique:students,email,{$id}",
            "phoneNumber" => "sometimes|string|max:255",
            "level" => "sometimes|in:One,Two,Three,Four",
            'specialization' => 'sometimes|in:CS,IT',
            'academic_year' => 'sometimes|string'
        ]);

        $updateData = $request->only([
            'code', 'name', 'email', 'phoneNumber',
            'level', 'specialization', 'academic_year'
        ]);

        if(empty($updateData)){
            return response()->json([
                'message' => 'يجب تقديم حقل واحد على الأقل للتحديث'
            ], 422);
        }

        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'message' => 'الطالب غير موجود'
            ], 404);
        }

        $student->update($updateData);

        $studentData = $student->makeHidden(['password', 'created_at', 'updated_at']);

        return response()->json([
            'student' => $studentData,
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/students/{id}",
     *     operationId="deleteStudent",
     *     tags={"Admin Students"},
     *     summary="Delete a student",
     *     description="Deletes a student record by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Student ID to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Student not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student not found")
     *         )
     *     )
     * )
     */
    public function delete($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'message' => 'Student not found'
            ], 404);
        }

        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully'
        ], 200);
    }

// /**
//  * @OA\Get(
//  *     path="/api/student/without-grades",
//  *     operationId="getStudentsWithoutGrades",
//  *     tags={"Student Grades"},
//  *     summary="Get students without grades",
//  *     description="Returns list of students who don't have grades with their missing subjects, with optional filtering",
//  *     @OA\Parameter(
//  *         name="level",
//  *         in="query",
//  *         description="Filter by student level",
//  *         required=false,
//  *         @OA\Schema(type="string", enum={"One", "Two", "Three", "Four"})
//  *     ),
//  *     @OA\Parameter(
//  *         name="academic_year",
//  *         in="query",
//  *         description="Filter by academic year",
//  *         required=false,
//  *         @OA\Schema(type="string")
//  *     ),
//  *     @OA\Parameter(
//  *         name="specialization",
//  *         in="query",
//  *         description="Filter by specialization",
//  *         required=false,
//  *         @OA\Schema(type="string", enum={"CS", "IT"})
//  *     ),
//  *     @OA\Response(
//  *         response=200,
//  *         description="Successful operation",
//  *         @OA\JsonContent(
//  *             type="array",
//  *             @OA\Items(
//  *                 type="object",
//  *                 @OA\Property(property="id", type="integer", example=1),
//  *                 @OA\Property(property="code", type="string", example="ST2023001"),
//  *                 @OA\Property(property="name", type="string", example="John Doe"),
//  *                 @OA\Property(property="email", type="string", example="john@example.com"),
//  *                 @OA\Property(property="phoneNumber", type="string", example="+1234567890"),
//  *                 @OA\Property(property="level", type="string", example="One"),
//  *                 @OA\Property(property="specialization", type="string", example="CS"),
//  *                 @OA\Property(property="academic_year", type="string", example="2023-2024"),
//  *                 @OA\Property(
//  *                     property="missing_subjects",
//  *                     type="array",
//  *                     @OA\Items(
//  *                         type="object",
//  *                         @OA\Property(property="subject_id", type="integer", example=1),
//  *                         @OA\Property(property="subject_name", type="string", example="Mathematics")
//  *                     )
//  *                 )
//  *             )
//  *         )
//  *     )
//  * )
//  */
// public function studentsWithoutGrades(Request $request)
// {
//     // Get current academic year from Admin table
//     $currentAcademicYear = Admin::value('academic_year') ?? date('Y').'-'.(date('Y')+1);

//     // Get all students based on filters
//     $query = Student::with(['grades' => function($query) {
//         $query->select('student_id', 'subject_id');
//     }])->select([
//         'id',
//         'code',
//         'name',
//         'email',
//         'phoneNumber',
//         'level',
//         'specialization',
//         'academic_year'
//     ]);

//     // Apply filters
//     if ($request->has('level')) {
//         $query->where('level', $request->level);
//     }

//     if ($request->has('academic_year')) {
//         $query->where('academic_year', $request->academic_year);
//     } else {
//         $query->where('academic_year', $currentAcademicYear);
//     }

//     if ($request->has('specialization')) {
//         $query->where('specialization', $request->specialization);
//     }

//     $students = $query->get();

//     // Get all subjects grouped by level and specialization
//     $subjectsQuery = Subject::select('id', 'name', 'level', 'specialization');

//     // Only add academic_year if the column exists in the subjects table
//     if (Schema::hasColumn('subjects', 'academic_year')) {
//         $subjectsQuery->addSelect('academic_year')
//             ->where('academic_year', $currentAcademicYear);
//     }

//     $subjects = $subjectsQuery
//         ->whereIn('level', $students->pluck('level')->unique())
//         ->whereIn('specialization', $students->pluck('specialization')->unique())
//         ->get()
//         ->groupBy(['level', 'specialization']);

//     // Prepare the response
//     $response = $students->map(function ($student) use ($subjects, $currentAcademicYear) {
//         // Get subjects for this student's level and specialization
//         $studentSubjects = $subjects->get($student->level, [])->get($student->specialization, []);

//         // Filter subjects by academic year if the column exists
//         if (Schema::hasColumn('subjects', 'academic_year')) {
//             $studentSubjects = collect($studentSubjects)->filter(function ($subject) use ($currentAcademicYear) {
//                 return $subject->academic_year == $currentAcademicYear;
//             });
//         }

//         // Get subject IDs that already have grades
//         $gradedSubjectIds = $student->grades->pluck('subject_id')->toArray();

//         // Find missing subjects
//         $missingSubjects = collect($studentSubjects)
//             ->reject(function ($subject) use ($gradedSubjectIds) {
//                 return in_array($subject->id, $gradedSubjectIds);
//             })
//             ->map(function ($subject) {
//                 return [
//                     'subject_id' => $subject->id,
//                     'subject_name' => $subject->name
//                 ];
//             })
//             ->values()
//             ->toArray();

//         // Add missing subjects to student data
//         return array_merge($student->toArray(), [
//             'missing_subjects' => $missingSubjects
//         ]);
//     });

//     // Filter out students who have all subjects graded
//     $filteredResponse = $response->filter(function ($student) {
//         return !empty($student['missing_subjects']);
//     })->values();

//     return response()->json($filteredResponse, 200);
// }
}

