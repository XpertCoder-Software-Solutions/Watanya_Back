<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Doctor;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Admin Subjects",
 * )
 * @OA\Tag(
 *     name="Doctor Subject",
 * )
 */
class SubjectsController
{
    /**
 * @OA\Get(
 *     path="/api/admin/subjects",
 *     tags={"Admin Subjects"},
 *     summary="Get paginated list of subjects with filtering and search",
 *     description="Returns a paginated list of all subjects with filtering by specialization, level, and search by subject code",
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
 *         name="specialization",
 *         in="query",
 *         description="Filter by specialization (CS, IT)",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             enum={"CS", "IT"}
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="level",
 *         in="query",
 *         description="Filter by level (One, Two, Three, Four)",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             enum={"One", "Two", "Three", "Four"}
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="semester",
 *         in="query",
 *         description="Filter by semester (One, Two)",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             enum={"One", "Two"}
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         description="Search by subject code",
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
 *             @OA\Property(property="total_items", type="integer", example=50),
 *             @OA\Property(
 *                 property="subjects",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="code", type="string", example="CS101"),
 *                     @OA\Property(property="name", type="string", example="Introduction to Computer Science"),
 *                     @OA\Property(property="creditHours", type="integer", example=3),
 *                     @OA\Property(property="specialization", type="string", enum={"CS", "IT"}, example="CS"),
 *                     @OA\Property(property="level", type="string", enum={"One", "Two", "Three", "Four"}, example="One"),
 *                     @OA\Property(property="semester", type="string", enum={"One", "Two"}, example="One")
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

    $query = Subject::query();

    // Apply filters
    if ($request->has('specialization') && in_array($request->specialization, ['CS', 'IT'])) {
        $query->where('specialization', $request->specialization);
    }

    if ($request->has('level') && in_array($request->level, ['One', 'Two', 'Three', 'Four'])) {
        $query->where('level', $request->level);
    }

    if ($request->has('semester') && in_array($request->semester, ['One', 'Two'])) {
        $query->where('semester', $request->semester);
    }

    // Apply search by subject code
    if ($request->has('search')) {
        $searchTerm = $request->input('search');
        $query->where('code', 'LIKE', "%{$searchTerm}%");
    }

    $paginatedSubjects = $query->select(['id', 'code', 'name', 'creditHours', 'specialization', 'level', 'semester'])
        ->orderBy('code')
        ->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
        'current_page' => $paginatedSubjects->currentPage(),
        'per_page' => $paginatedSubjects->perPage(),
        'total_pages' => $paginatedSubjects->lastPage(),
        'total_items' => $paginatedSubjects->total(),
        'subjects' => $paginatedSubjects->items()
    ], 200);
}

    /**
     * @OA\Post(
     *     path="/api/admin/subjects",
     *     tags={"Admin Subjects"},
     *     summary="Create a new subject",
     *     description="Create a new subject with the specified data",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code", "name", "creditHours", "specialization", "level", "semester"},
     *             @OA\Property(property="code", type="string", example="CS101"),
     *             @OA\Property(property="name", type="string", example="Introduction to Computer Science"),
     *             @OA\Property(property="creditHours", type="integer", example=3),
     *             @OA\Property(property="specialization", type="string", enum={"CS", "IT"}, example="CS"),
     *             @OA\Property(property="level", type="string", enum={"One", "Two", "Three", "Four"}, example="One"),
     *             @OA\Property(property="semester", type="string", enum={"One", "Two"}, example="One")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subject created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="subject",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="code", type="string", example="CS101"),
     *                 @OA\Property(property="name", type="string", example="Introduction to Computer Science"),
     *                 @OA\Property(property="creditHours", type="integer", example=3),
     *                 @OA\Property(property="specialization", type="string", example="CS"),
     *                 @OA\Property(property="level", type="string", example="One"),
     *                 @OA\Property(property="semester", type="string", example="One")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:subjects,code',
            'name' => 'required|string|max:255',
            'creditHours' => 'required|integer|min:1',
            'specialization' => 'required|in:CS,IT',
            'level' => 'required|in:One,Two,Three,Four',
            'semester' => 'required|in:One,Two',
        ]);

        $subject = Subject::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'creditHours' => $validated['creditHours'],
            'specialization' => $validated['specialization'],
            'level' => $validated['level'],
            'semester' => $validated['semester'],
        ]);

        return response()->json([
            "subject" => [
                'id' => $subject->id,
                'code' => $subject->code,
                'name' => $subject->name,
                'creditHours' => $subject->creditHours,
                'specialization' => $subject->specialization,
                'level' => $subject->level,
                'semester' => $subject->semester
            ]
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/subjects/{id}",
     *     tags={"Admin Subjects"},
     *     summary="Update a subject",
     *     description="Update an existing subject's data",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the subject to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code", "name", "creditHours", "specialization", "level", "semester"},
     *             @OA\Property(property="code", type="string", example="CS101"),
     *             @OA\Property(property="name", type="string", example="Introduction to Computer Science (Updated)"),
     *             @OA\Property(property="creditHours", type="integer", example=4),
     *             @OA\Property(property="specialization", type="string", enum={"CS", "IT"}, example="CS"),
     *             @OA\Property(property="level", type="string", enum={"One", "Two", "Three", "Four"}, example="Two"),
     *             @OA\Property(property="semester", type="string", enum={"One", "Two"}, example="Two")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subject updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="subject",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="code", type="string", example="CS101"),
     *                 @OA\Property(property="name", type="string", example="Introduction to Computer Science (Updated)"),
     *                 @OA\Property(property="creditHours", type="integer", example=4),
     *                 @OA\Property(property="specialization", type="string", example="CS"),
     *                 @OA\Property(property="level", type="string", example="Two"),
     *                 @OA\Property(property="semester", type="string", example="Two")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subject not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json(['message' => 'Subject not found'], 404);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:subjects,code,'.$id,
            'name' => 'required|string|max:255',
            'creditHours' => 'required|integer|min:1',
            'specialization' => 'required|in:CS,IT',
            'level' => 'required|in:One,Two,Three,Four',
            'semester' => 'required|in:One,Two',
        ]);

        $subject->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'creditHours' => $validated['creditHours'],
            'specialization' => $validated['specialization'],
            'level' => $validated['level'],
            'semester' => $validated['semester'],
        ]);

        return response()->json([
            "subject" => [
                'id' => $subject->id,
                'code' => $subject->code,
                'name' => $subject->name,
                'creditHours' => $subject->creditHours,
                'specialization' => $subject->specialization,
                'level' => $subject->level,
                'semester' => $subject->semester
            ]
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/subjects/{id}",
     *     tags={"Admin Subjects"},
     *     summary="Delete a subject",
     *     description="Delete a subject by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the subject to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subject deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Subject deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subject not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Subject not found")
     *         )
     *     )
     * )
     */
    public function delete($id)
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json(['message' => 'Subject not found'], 404);
        }

        $subject->delete();

        return response()->json(['message' => 'Subject deleted successfully'], 200);
    }

/**
     * @OA\Get(
     *     path="/api/doctor/{doctor_id}/subjects",
     *     tags={"Doctor Subject"},
     *     summary="Get all subjects for specific doctor",
     *     description="Returns paginated data for doctor's subjects with filtering and search",
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="path",
     *         description="ID of the doctor",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
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
     *         name="specialization",
     *         in="query",
     *         description="Filter by specialization (CS, IT)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"CS", "IT"})
     *     ),
     *     @OA\Parameter(
     *         name="level",
     *         in="query",
     *         description="Filter by level (One, Two, Three, Four)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"One", "Two", "Three", "Four"})
     *     ),
     *     @OA\Parameter(
     *         name="semester",
     *         in="query",
     *         description="Filter by semester (One, Two)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"One", "Two"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by subject code",
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
     *             @OA\Property(property="total_items", type="integer", example=50),
     *             @OA\Property(
     *                 property="subjects",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="CS101"),
     *                     @OA\Property(property="name", type="string", example="Introduction to Computer Science"),
     *                     @OA\Property(property="creditHours", type="integer", example=3),
     *                     @OA\Property(property="specialization", type="string", enum={"CS", "IT"}, example="CS"),
     *                     @OA\Property(property="level", type="string", enum={"One", "Two", "Three", "Four"}, example="One"),
     *                     @OA\Property(property="semester", type="string", enum={"One", "Two"}, example="One"),
     *                     @OA\Property(property="totalGrade", type="number", format="float", example=100),
     *                     @OA\Property(property="yearsWorkGrade", type="number", format="float", example=20),
     *                     @OA\Property(property="midtermGrade", type="number", format="float", example=20),
     *                     @OA\Property(property="finalGrade", type="number", format="float", example=50),
     *                     @OA\Property(property="practicalGrade", type="number", format="float", example=10)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Doctor not found")
     *         )
     *     )
     * )
     */
    public function getDoctorSubjects(Request $request, $doctor_id)
    {
        $doctor = Doctor::find($doctor_id);
        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $query = $doctor->subjects();

        // Apply filters
        if ($request->has('specialization') && in_array($request->specialization, ['CS', 'IT'])) {
            $query->where('specialization', $request->specialization);
        }

        if ($request->has('level') && in_array($request->level, ['One', 'Two', 'Three', 'Four'])) {
            $query->where('level', $request->level);
        }

        if ($request->has('semester') && in_array($request->semester, ['One', 'Two'])) {
            $query->where('semester', $request->semester);
        }

        // Apply search by subject code
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('code', 'LIKE', "%{$searchTerm}%");
        }

        $subjects = $query->select([
                'subjects.id',
                'subjects.code',
                'subjects.name',
                'subjects.creditHours',
                'subjects.specialization',
                'subjects.level',
                'subjects.semester',
                'subjects.totalGrade',
                'subjects.yearsWorkGrade',
                'subjects.midtermGrade',
                'subjects.finalGrade',
                'subjects.practicalGrade'
            ])
            ->orderBy('subjects.code')
            ->paginate($perPage, ['*'], 'page', $page);

        // Remove unwanted fields
        $filteredSubjects = collect($subjects->items())->map(function ($subject) {
            return collect($subject)->except(['pivot', 'created_at', 'updated_at'])->all();
        });

        return response()->json([
            'current_page' => $subjects->currentPage(),
            'per_page' => $subjects->perPage(),
            'total_pages' => $subjects->lastPage(),
            'total_items' => $subjects->total(),
            'subjects' => $filteredSubjects
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/doctor/{doctor_id}/subjects/without-grades",
     *     tags={"Doctor Subject"},
     *     summary="Get doctor's subjects without grade data",
     *     description="Returns paginated list of doctor's subjects that don't have grade fields set",
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="path",
     *         description="ID of the doctor",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
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
     *         name="specialization",
     *         in="query",
     *         description="Filter by specialization (CS, IT)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"CS", "IT"})
     *     ),
     *     @OA\Parameter(
     *         name="level",
     *         in="query",
     *         description="Filter by level (One, Two, Three, Four)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"One", "Two", "Three", "Four"})
     *     ),
     *     @OA\Parameter(
     *         name="semester",
     *         in="query",
     *         description="Filter by semester (One, Two)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"One", "Two"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by subject code",
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
     *             @OA\Property(property="total_items", type="integer", example=50),
     *             @OA\Property(
     *                 property="subjects",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="CS101"),
     *                     @OA\Property(property="name", type="string", example="Introduction to Computer Science"),
     *                     @OA\Property(property="creditHours", type="integer", example=3),
     *                     @OA\Property(property="specialization", type="string", enum={"CS", "IT"}, example="CS"),
     *                     @OA\Property(property="level", type="string", enum={"One", "Two", "Three", "Four"}, example="One"),
     *                     @OA\Property(property="semester", type="string", enum={"One", "Two"}, example="One")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Doctor not found")
     *         )
     *     )
     * )
     */
    public function getDoctorSubjectsWithoutGrades(Request $request, $doctor_id)
    {
        $doctor = Doctor::find($doctor_id);
        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $query = $doctor->subjects()
            ->whereNull('totalGrade')
            ->whereNull('yearsWorkGrade')
            ->whereNull('midtermGrade')
            ->whereNull('finalGrade')
            ->whereNull('practicalGrade');

        // Apply filters
        if ($request->has('specialization') && in_array($request->specialization, ['CS', 'IT'])) {
            $query->where('specialization', $request->specialization);
        }

        if ($request->has('level') && in_array($request->level, ['One', 'Two', 'Three', 'Four'])) {
            $query->where('level', $request->level);
        }

        if ($request->has('semester') && in_array($request->semester, ['One', 'Two'])) {
            $query->where('semester', $request->semester);
        }

        // Apply search by subject code
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('code', 'LIKE', "%{$searchTerm}%");
        }

        $subjects = $query->select([
                'subjects.id',
                'subjects.code',
                'subjects.name',
                'subjects.creditHours',
                'subjects.specialization',
                'subjects.level',
                'subjects.semester'
            ])
            ->orderBy('subjects.code')
            ->paginate($perPage, ['*'], 'page', $page);

        // Remove unwanted fields
        $filteredSubjects = collect($subjects->items())->map(function ($subject) {
            return collect($subject)->except(['pivot', 'created_at', 'updated_at'])->all();
        });

        return response()->json([
            'current_page' => $subjects->currentPage(),
            'per_page' => $subjects->perPage(),
            'total_pages' => $subjects->lastPage(),
            'total_items' => $subjects->total(),
            'subjects' => $filteredSubjects
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/doctor/subject/{subject_id}",
     *     tags={"Doctor Subject"},
     *     summary="Update subject grades by doctor",
     *     description="Update grading parameters for a subject (only accessible by doctors)",
     *     @OA\Parameter(
     *         name="subject_id",
     *         in="path",
     *         description="ID of the subject to update grades for",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="totalGrade", type="number", format="float", example=100),
     *             @OA\Property(property="yearsWorkGrade", type="number", format="float", example=20),
     *             @OA\Property(property="midtermGrade", type="number", format="float", example=20),
     *             @OA\Property(property="finalGrade", type="number", format="float", example=50),
     *             @OA\Property(property="practicalGrade", type="number", format="float", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subject grades updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="subject",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="totalGrade", type="number", format="float", example=100),
     *                 @OA\Property(property="yearsWorkGrade", type="number", format="float", example=20),
     *                 @OA\Property(property="midtermGrade", type="number", format="float", example=20),
     *                 @OA\Property(property="finalGrade", type="number", format="float", example=50),
     *                 @OA\Property(property="practicalGrade", type="number", format="float", example=10)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subject not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Subject not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function updateSubjectGrades(Request $request, $subject_id)
    {
        $subject = Subject::find($subject_id);

        if (!$subject) {
            return response()->json(['message' => 'Subject not found'], 404);
        }

        $validated = $request->validate([
            'totalGrade' => 'required|numeric|min:0',
            'yearsWorkGrade' => 'required|numeric|min:0',
            'midtermGrade' => 'required|numeric|min:0',
            'finalGrade' => 'required|numeric|min:0',
            'practicalGrade' => 'required|numeric|min:0',
        ]);

        $sum = $validated['yearsWorkGrade'] + $validated['midtermGrade'] +
               $validated['finalGrade'] + $validated['practicalGrade'];

        if (abs($sum - $validated['totalGrade']) > 0.01) {
            return response()->json([
                'message' => 'The sum of yearsWorkGrade, midtermGrade, finalGrade, and practicalGrade must equal totalGrade'
            ], 422);
        }

        $subject->update([
            'totalGrade' => $validated['totalGrade'],
            'yearsWorkGrade' => $validated['yearsWorkGrade'],
            'midtermGrade' => $validated['midtermGrade'],
            'finalGrade' => $validated['finalGrade'],
            'practicalGrade' => $validated['practicalGrade'],
        ]);

        return response()->json([
            'subject' => [
                'id' => $subject->id,
                'totalGrade' => $subject->totalGrade,
                'yearsWorkGrade' => $subject->yearsWorkGrade,
                'midtermGrade' => $subject->midtermGrade,
                'finalGrade' => $subject->finalGrade,
                'practicalGrade' => $subject->practicalGrade
            ]
        ], 200);
    }
}
