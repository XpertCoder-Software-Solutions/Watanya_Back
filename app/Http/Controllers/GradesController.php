<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Admin;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Student Grades",
 * )
 */
class GradesController
{
    /**
     * @OA\Post(
     *     path="/api/student/{student_id}/grades",
     *     tags={"Student Grades"},
     *     summary="Add student grades",
     *     description="Store student grade information in grades table for specific subject",
     *     @OA\Parameter(
     *         name="student_id",
     *         in="path",
     *         description="ID of the student",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"subject_id", "midtermGrade", "practicalGrade", "yearsWorkGrade", "finalGrade", "gradeStatus"},
     *             @OA\Property(property="subject_id", type="integer", description="Subject ID", example=1),
     *             @OA\Property(property="midtermGrade", type="number", format="float", minimum=0, maximum=100, example=25),
     *             @OA\Property(property="practicalGrade", type="number", format="float", minimum=0, maximum=100, example=15),
     *             @OA\Property(property="yearsWorkGrade", type="number", format="float", minimum=0, maximum=100, example=20),
     *             @OA\Property(property="finalGrade", type="number", format="float", minimum=0, maximum=100, example=40),
     *             @OA\Property(property="gradeStatus", type="string", enum={"pass", "i", "i*", "ff*", "others"}, example="pass")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grade created successfully in grades table",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="grade",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="student_id", type="integer", example=1),
     *                 @OA\Property(property="subject_id", type="integer", example=1),
     *                 @OA\Property(property="totalGrade", type="number", format="float", example=100),
     *                 @OA\Property(property="totalGradeChar", type="string", example="A"),
     *                 @OA\Property(property="midtermGrade", type="number", format="float", example=25),
     *                 @OA\Property(property="practicalGrade", type="number", format="float", example=15),
     *                 @OA\Property(property="yearsWorkGrade", type="number", format="float", example=20),
     *                 @OA\Property(property="finalGrade", type="number", format="float", example=40),
     *                 @OA\Property(property="gradeStatus", type="string", example="pass"),
     *                 @OA\Property(property="academic_year", type="string", example="2023-2024")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflict - Grade already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Grade already exists for this student in the specified subject"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="subject_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="This student already has a grade record for this subject")
     *                 )
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
     *                     property="subject_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected subject_id is invalid.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student or subject not found")
     *         )
     *     )
     * )
     */
    public function store(Request $request, $student_id)
    {
        // Validate the request data including gradeStatus
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'midtermGrade' => 'required|numeric|min:0|max:100',
            'practicalGrade' => 'required|numeric|min:0|max:100',
            'yearsWorkGrade' => 'required|numeric|min:0|max:100',
            'finalGrade' => 'required|numeric|min:0|max:100',
            'gradeStatus' => 'required|in:pass,i,i*,ff*,others',
        ]);

        // Check if grade already exists for this student and subject
        $existingGrade = Grade::where('student_id', $student_id)
                            ->where('subject_id', $validated['subject_id'])
                            ->first();

        if ($existingGrade) {
            return response()->json([
                'message' => 'Grade already exists for this student in the specified subject',
                'errors' => [
                    'subject_id' => ['This student already has a grade record for this subject']
                ]
            ], 409);
        }

        // Find student and subject
        $student = Student::findOrFail($student_id);
        $subject = Subject::findOrFail($validated['subject_id']);

        // Compare grades with subject maximum grades
        if ($validated['midtermGrade'] > $subject->midtermGrade) {
            return response()->json([
                'message' => 'Midterm grade exceeds maximum allowed for this subject',
                'errors' => [
                    'midtermGrade' => ['The midterm grade cannot exceed ' . $subject->midtermGrade]
                ]
            ], 422);
        }

        if ($validated['practicalGrade'] > $subject->practicalGrade) {
            return response()->json([
                'message' => 'Practical grade exceeds maximum allowed for this subject',
                'errors' => [
                    'practicalGrade' => ['The practical grade cannot exceed ' . $subject->practicalGrade]
                ]
            ], 422);
        }

        if ($validated['yearsWorkGrade'] > $subject->yearsWorkGrade) {
            return response()->json([
                'message' => 'Years work grade exceeds maximum allowed for this subject',
                'errors' => [
                    'yearsWorkGrade' => ['The years work grade cannot exceed ' . $subject->yearsWorkGrade]
                ]
            ], 422);
        }

        if ($validated['finalGrade'] > $subject->finalGrade) {
            return response()->json([
                'message' => 'Final grade exceeds maximum allowed for this subject',
                'errors' => [
                    'finalGrade' => ['The final grade cannot exceed ' . $subject->finalGrade]
                ]
            ], 422);
        }

        // Calculate total grade
        $totalGrade = $validated['midtermGrade'] +
                     $validated['practicalGrade'] +
                     $validated['yearsWorkGrade'] +
                     $validated['finalGrade'];

        // Validate total grade doesn't exceed subject's maximum total grade if exists
        if ($subject->totalGrade && $totalGrade > $subject->totalGrade) {
            return response()->json([
                'message' => 'Total grade exceeds maximum allowed for this subject',
                'errors' => [
                    'total' => ['The total grade cannot exceed ' . $subject->totalGrade]
                ]
            ], 422);
        }

        // Get the academic year from Admin table
        $admin = Admin::first();
        $academicYear = $admin ? $admin->academic_year : date('Y').'-'.(date('Y')+1);

        // Create grade record in grades table
        $gradeData = [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'totalGrade' => $totalGrade,
            'totalGradeChar' => $this->convertToGradeChar($totalGrade),
            'midtermGrade' => $validated['midtermGrade'],
            'practicalGrade' => $validated['practicalGrade'],
            'yearsWorkGrade' => $validated['yearsWorkGrade'],
            'finalGrade' => $validated['finalGrade'],
            'gradeStatus' => $validated['gradeStatus'],
            'academic_year' => $academicYear,
        ];

        $grade = Grade::create($gradeData);

        // Remove timestamps from the response
        $responseData = [
            'grade' => collect($grade)->except(['created_at', 'updated_at'])->toArray()
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Convert numeric grade to letter grade
     *
     * @param float $totalGrade
     * @return string
     */
    private function convertToGradeChar($totalGrade)
    {
        if ($totalGrade >= 80) return 'A';
        if ($totalGrade >= 75) return 'B+';
        if ($totalGrade >= 65) return 'B';
        if ($totalGrade >= 60) return 'C+';
        if ($totalGrade >= 50) return 'C';
        return 'F';
    }
}
