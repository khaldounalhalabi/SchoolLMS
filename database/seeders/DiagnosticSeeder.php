<?php

namespace Database\Seeders;

use App\Models\DiagnosticAnswer;
use App\Models\DiagnosticAttempt;
use App\Models\DiagnosticQuestion;
use App\Models\KnowledgeMapResult;
use App\Models\LearningObjective;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiagnosticSeeder extends Seeder
{
    public function run(): void
    {
        $math    = Subject::where('code', 'MATH')->first();
        $science = Subject::where('code', 'SCI')->first();
        $english = Subject::where('code', 'ENG')->first();

        if (! $math || ! $science || ! $english) {
            $this->command->warn('Subjects not found. Run DatabaseSeeder first.');
            return;
        }

        // ---- Learning Objectives ----

        // Mathematics
        $mathAlgebra = LearningObjective::firstOrCreate(
            ['subject_id' => $math->id, 'name' => 'Algebra', 'parent_id' => null],
            ['description' => 'Algebraic expressions, equations and inequalities']
        );
        $mathLinear = LearningObjective::firstOrCreate(
            ['subject_id' => $math->id, 'name' => 'Linear Equations', 'parent_id' => $mathAlgebra->id],
            ['description' => 'Solving and graphing linear equations']
        );
        $mathQuadratic = LearningObjective::firstOrCreate(
            ['subject_id' => $math->id, 'name' => 'Quadratic Equations', 'parent_id' => $mathAlgebra->id],
            ['description' => 'Factoring and solving quadratic equations']
        );
        $mathGeometry = LearningObjective::firstOrCreate(
            ['subject_id' => $math->id, 'name' => 'Geometry', 'parent_id' => null],
            ['description' => 'Shapes, angles, and spatial reasoning']
        );
        $mathAngles = LearningObjective::firstOrCreate(
            ['subject_id' => $math->id, 'name' => 'Angles & Triangles', 'parent_id' => $mathGeometry->id],
            ['description' => 'Properties of angles and triangle theorems']
        );
        $mathArea = LearningObjective::firstOrCreate(
            ['subject_id' => $math->id, 'name' => 'Area & Perimeter', 'parent_id' => $mathGeometry->id],
            ['description' => 'Calculating area and perimeter of 2D shapes']
        );

        // Science
        $sciCells = LearningObjective::firstOrCreate(
            ['subject_id' => $science->id, 'name' => 'Cell Biology', 'parent_id' => null],
            ['description' => 'Structure and function of cells']
        );
        $sciCellStructure = LearningObjective::firstOrCreate(
            ['subject_id' => $science->id, 'name' => 'Cell Structure', 'parent_id' => $sciCells->id],
            ['description' => 'Organelles and their functions']
        );
        $sciCellDivision = LearningObjective::firstOrCreate(
            ['subject_id' => $science->id, 'name' => 'Cell Division', 'parent_id' => $sciCells->id],
            ['description' => 'Mitosis and meiosis']
        );
        $sciPhysics = LearningObjective::firstOrCreate(
            ['subject_id' => $science->id, 'name' => 'Forces & Motion', 'parent_id' => null],
            ['description' => "Newton's laws and mechanics"]
        );
        $sciNewton = LearningObjective::firstOrCreate(
            ['subject_id' => $science->id, 'name' => "Newton's Laws", 'parent_id' => $sciPhysics->id],
            ['description' => 'Three laws of motion with examples']
        );

        // English
        $engGrammar = LearningObjective::firstOrCreate(
            ['subject_id' => $english->id, 'name' => 'Grammar', 'parent_id' => null],
            ['description' => 'English grammar rules and usage']
        );
        $engTenses = LearningObjective::firstOrCreate(
            ['subject_id' => $english->id, 'name' => 'Verb Tenses', 'parent_id' => $engGrammar->id],
            ['description' => 'Present, past and future tenses']
        );
        $engReading = LearningObjective::firstOrCreate(
            ['subject_id' => $english->id, 'name' => 'Reading Comprehension', 'parent_id' => null],
            ['description' => 'Understanding and interpreting written text']
        );

        // ---- Questions ----

        $this->addQuestion($mathLinear, $math, 'mcq',
            'What is the solution of 2x + 4 = 10?',
            ['x = 2', 'x = 3', 'x = 4', 'x = 7'], 1
        );
        $this->addQuestion($mathLinear, $math, 'mcq',
            'Which of the following is a linear equation?',
            ['y = x²', 'y = 2x + 1', 'y = √x', 'y = 1/x'], 1
        );
        $this->addQuestion($mathLinear, $math, 'true_false',
            'The equation y = 3x - 5 has a slope of 3.',
            ['True', 'False'], 0
        );
        $this->addQuestion($mathLinear, $math, 'mcq',
            'If 5x - 15 = 0, then x equals:',
            ['1', '3', '5', '15'], 1
        );

        $this->addQuestion($mathQuadratic, $math, 'mcq',
            'What are the roots of x² - 5x + 6 = 0?',
            ['x = 1 and x = 6', 'x = 2 and x = 3', 'x = -2 and x = -3', 'x = 0 and x = 5'], 1
        );
        $this->addQuestion($mathQuadratic, $math, 'true_false',
            'A quadratic equation always has two distinct real roots.',
            ['True', 'False'], 1
        );
        $this->addQuestion($mathQuadratic, $math, 'mcq',
            'Which method can be used to solve x² - 4 = 0?',
            ['Substitution', 'Factoring', 'Graphing only', 'Elimination'], 1
        );

        $this->addQuestion($mathAngles, $math, 'mcq',
            'The sum of interior angles of a triangle is:',
            ['90°', '180°', '270°', '360°'], 1
        );
        $this->addQuestion($mathAngles, $math, 'true_false',
            'An obtuse angle measures more than 90°.',
            ['True', 'False'], 0
        );
        $this->addQuestion($mathAngles, $math, 'mcq',
            'Two angles that add up to 90° are called:',
            ['Supplementary', 'Complementary', 'Vertical', 'Adjacent'], 1
        );

        $this->addQuestion($mathArea, $math, 'mcq',
            'What is the area of a rectangle with length 8 and width 5?',
            ['13', '26', '40', '80'], 2
        );
        $this->addQuestion($mathArea, $math, 'mcq',
            'The perimeter of a square with side 6 is:',
            ['12', '24', '36', '6'], 1
        );
        $this->addQuestion($mathArea, $math, 'true_false',
            'The area of a circle with radius r is πr².',
            ['True', 'False'], 0
        );

        // Science questions
        $this->addQuestion($sciCellStructure, $science, 'mcq',
            'Which organelle is known as the powerhouse of the cell?',
            ['Nucleus', 'Ribosome', 'Mitochondria', 'Vacuole'], 2
        );
        $this->addQuestion($sciCellStructure, $science, 'mcq',
            'Which structure controls the movement of materials in and out of the cell?',
            ['Cell wall', 'Cell membrane', 'Nucleus', 'Cytoplasm'], 1
        );
        $this->addQuestion($sciCellStructure, $science, 'true_false',
            'Plant cells have a cell wall, but animal cells do not.',
            ['True', 'False'], 0
        );
        $this->addQuestion($sciCellStructure, $science, 'mcq',
            'Where is DNA stored in a eukaryotic cell?',
            ['Mitochondria', 'Ribosome', 'Nucleus', 'Cytoplasm'], 2
        );

        $this->addQuestion($sciCellDivision, $science, 'mcq',
            'Mitosis results in how many daughter cells?',
            ['1', '2', '4', '8'], 1
        );
        $this->addQuestion($sciCellDivision, $science, 'true_false',
            'Meiosis produces cells with the same chromosome number as the parent cell.',
            ['True', 'False'], 1
        );
        $this->addQuestion($sciCellDivision, $science, 'mcq',
            'Which phase of mitosis do chromosomes line up in the middle of the cell?',
            ['Prophase', 'Metaphase', 'Anaphase', 'Telophase'], 1
        );

        $this->addQuestion($sciNewton, $science, 'mcq',
            "Newton's First Law states that an object at rest will:",
            ['Accelerate slowly', 'Stay at rest unless acted upon by a force', 'Always move in a circle', 'Gain mass over time'], 1
        );
        $this->addQuestion($sciNewton, $science, 'mcq',
            'Force equals mass times:',
            ['Speed', 'Velocity', 'Acceleration', 'Distance'], 2
        );
        $this->addQuestion($sciNewton, $science, 'true_false',
            "Newton's Third Law states that for every action there is an equal and opposite reaction.",
            ['True', 'False'], 0
        );

        // English questions
        $this->addQuestion($engTenses, $english, 'mcq',
            'Which sentence is in the simple past tense?',
            ['She is reading.', 'She reads every day.', 'She read the book yesterday.', 'She will read tomorrow.'], 2
        );
        $this->addQuestion($engTenses, $english, 'mcq',
            'What is the correct past tense of "go"?',
            ['Goed', 'Goes', 'Went', 'Gone'], 2
        );
        $this->addQuestion($engTenses, $english, 'true_false',
            '"He has finished his homework" is in the present perfect tense.',
            ['True', 'False'], 0
        );
        $this->addQuestion($engTenses, $english, 'mcq',
            'Choose the correct future tense: "Tomorrow I ___ to school."',
            ['went', 'go', 'will go', 'going'], 2
        );

        $this->addQuestion($engReading, $english, 'mcq',
            'The main idea of a paragraph is usually found in the:',
            ['Last sentence', 'Middle sentence', 'Topic sentence', 'Any sentence'], 2
        );
        $this->addQuestion($engReading, $english, 'true_false',
            'Inference means understanding something that is directly stated in the text.',
            ['True', 'False'], 1
        );
        $this->addQuestion($engReading, $english, 'mcq',
            'A synonym is a word that has:',
            ['The opposite meaning', 'A similar meaning', 'No meaning', 'A rhyming sound'], 1
        );

        // ---- Attempts & answers for demo students ----
        $demoStudent = User::where('email', 'student@school.test')->first();
        $student1    = User::where('email', 'student1@school.test')->first();
        $student2    = User::where('email', 'student2@school.test')->first();

        foreach ([$demoStudent, $student1, $student2] as $student) {
            if (! $student) continue;

            foreach ([$math, $science] as $subject) {
                $existing = DiagnosticAttempt::where('student_user_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->whereNotNull('completed_at')
                    ->first();
                if ($existing) continue;

                $attempt = DiagnosticAttempt::create([
                    'student_user_id' => $student->id,
                    'subject_id'      => $subject->id,
                    'started_at'      => now()->subHours(2),
                    'completed_at'    => now()->subHour(),
                ]);

                $questions = DiagnosticQuestion::where('subject_id', $subject->id)
                    ->with('options')
                    ->get();

                foreach ($questions as $q) {
                    $correct = $q->options->firstWhere('is_correct', true);
                    $wrong   = $q->options->firstWhere('is_correct', false);
                    // 70% chance of correct answer
                    $pickCorrect = rand(1, 10) <= 7;
                    $chosen = $pickCorrect ? $correct : $wrong;

                    DiagnosticAnswer::create([
                        'attempt_id'         => $attempt->id,
                        'question_id'        => $q->id,
                        'selected_option_id' => $chosen?->id,
                        'is_correct'         => $pickCorrect,
                    ]);
                }

                // Calculate and upsert knowledge_map_results
                $answers = $attempt->answers()->with('question')->get();
                $byObjective = $answers->groupBy('question.learning_objective_id');

                foreach ($byObjective as $objectiveId => $objectiveAnswers) {
                    $total   = $objectiveAnswers->count();
                    $correct = $objectiveAnswers->where('is_correct', true)->count();
                    $mastery = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

                    KnowledgeMapResult::updateOrCreate(
                        ['student_user_id' => $student->id, 'learning_objective_id' => $objectiveId],
                        ['mastery_percent' => $mastery, 'last_assessed_at' => now()]
                    );
                }
            }
        }
    }

    private function addQuestion(LearningObjective $objective, $subject, string $type, string $text, array $options, int $correctIndex): void
    {
        $existing = DiagnosticQuestion::where('question_text', $text)
            ->where('subject_id', $subject->id)
            ->first();
        if ($existing) return;

        $question = DiagnosticQuestion::create([
            'subject_id'            => $subject->id,
            'learning_objective_id' => $objective->id,
            'question_text'         => $text,
            'type'                  => $type,
        ]);

        foreach ($options as $i => $optionText) {
            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $optionText,
                'is_correct'  => $i === $correctIndex,
            ]);
        }
    }
}
