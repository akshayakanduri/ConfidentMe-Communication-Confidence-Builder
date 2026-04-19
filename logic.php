<?xml version="1.0" encoding="UTF-8"?>
<?php
session_start();

// Initialize Session Variables
if (!isset($_SESSION['dark_mode'])) $_SESSION['dark_mode'] = false;
if (!isset($_SESSION['streak'])) $_SESSION['streak'] = 0;
if (!isset($_SESSION['score'])) $_SESSION['score'] = 0;
if (!isset($_SESSION['tasks_completed'])) $_SESSION['tasks_completed'] = 0;

// Load XML Data
$xmlData = simplexml_load_file(__DIR__ . '/data.xml');

// Helper Functions
function toggleDarkMode() {
    $_SESSION['dark_mode'] = !$_SESSION['dark_mode'];
}

function processMood($input) {
    global $xmlData;
    $inputLower = strtolower($input);
    $matchedMood = null;
    
    foreach ($xmlData->moods->mood as $mood) {
        $keywords = explode(',', (string)$mood->keywords);
        foreach ($keywords as $word) {
            if (strpos($inputLower, trim($word)) !== false) {
                $matchedMood = $mood;
                break 2;
            }
        }
    }
    
    return $matchedMood;
}

function evaluateSpeech($text) {
    $wordCount = str_word_count($text);
    $score = 0;
    $feedback = [];
    
    if ($wordCount > 15) {
        $score += 40;
        $feedback[] = "Good length. You provided enough detail.";
    } else {
        $score += 20;
        $feedback[] = "Try to elaborate a bit more for clarity.";
    }
    
    $confidentWords = ['confident', 'believe', 'sure', 'definitely', 'excited', 'passionate', 'experience', 'can', 'will'];
    $hesitantWords = ['maybe', 'um', 'uh', 'sorry', 'guess', 'think', 'probably'];
    
    $confidentCount = 0;
    $hesitantCount = 0;
    
    $words = explode(' ', strtolower($text));
    foreach ($words as $word) {
        if (in_array(trim($word, ".,?!"), $confidentWords)) $confidentCount++;
        if (in_array(trim($word, ".,?!"), $hesitantWords)) $hesitantCount++;
    }
    
    if ($confidentCount > 0) {
        $score += 30;
        $feedback[] = "Great use of positive, confident language!";
    }
    
    if ($hesitantCount > 0) {
        $score -= 10;
        $feedback[] = "Avoid using words like 'maybe' or 'um'. Speak with certainty.";
    } else {
        $score += 30;
    }
    
    $score = max(0, min(100, $score));
    
    // Update global progress
    $_SESSION['score'] = round(($_SESSION['score'] + $score) / 2);
    
    return ['score' => $score, 'feedback' => $feedback];
}

function getDailyChallenge() {
    global $xmlData;
    // For simplicity, pick a random one
    $count = count($xmlData->challenges->challenge);
    $index = rand(0, $count - 1);
    return $xmlData->challenges->challenge[$index];
}

function switchPersonality($text) {
    // Basic substitution for demonstration
    $shy = str_replace(
        ['I want', 'Give me', 'I think', 'Yes', 'No'],
        ['Um, maybe I would like', 'Could I possibly have', 'I guess maybe', 'Yeah, I suppose', 'I am not sure'],
        $text
    );
    
    $confident = str_replace(
        ['I think', 'maybe', 'I guess', 'sorry', 'try'],
        ['I believe', 'definitely', 'I know', 'excuse me', 'will do'],
        $text
    );
    
    // Add exclamation to confident
    if (substr($confident, -1) == '.') {
        $confident = substr($confident, 0, -1) . '!';
    }
    
    return [
        'shy' => $shy,
        'normal' => $text,
        'confident' => $confident
    ];
}

function getOverthinkingAdvice() {
    global $xmlData;
    $count = count($xmlData->overthinking->advice);
    $index = rand(0, $count - 1);
    return $xmlData->overthinking->advice[$index];
}

// Form Handling
$activeTab = 'mood'; // default
$moodResult = null;
$speechResult = null;
$personalityResult = null;
$overthinkingResult = null;
$dailyChallenge = getDailyChallenge();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'toggle_dark_mode':
                toggleDarkMode();
                break;
                
            case 'check_mood':
                $activeTab = 'mood';
                if (!empty($_POST['mood_text'])) {
                    $moodResult = processMood($_POST['mood_text']);
                    if (!$moodResult) {
                        $moodResult = (object)[
                            'emotion' => 'Neutral',
                            'explanation' => 'Sometimes it\'s hard to put feelings into words. That\'s okay.',
                            'actions' => (object)['action' => ['Take a short walk.', 'Drink some water.']],
                            'quote' => 'Every day is a fresh start.',
                            'actionSuggestion' => 'Take 5 minutes for yourself.'
                        ];
                    }
                }
                break;
                
            case 'evaluate_speech':
                $activeTab = 'practice';
                if (!empty($_POST['speech_text'])) {
                    $speechResult = evaluateSpeech($_POST['speech_text']);
                    $_SESSION['tasks_completed']++;
                }
                break;
                
            case 'complete_challenge':
                $activeTab = 'challenge';
                $_SESSION['streak']++;
                $_SESSION['tasks_completed']++;
                break;
                
            case 'mark_video_done':
                $activeTab = 'video';
                $_SESSION['streak']++;
                $_SESSION['tasks_completed']++;
                $_SESSION['score'] += 10;
                break;
                
            case 'switch_personality':
                $activeTab = 'personality';
                if (!empty($_POST['personality_text'])) {
                    $personalityResult = switchPersonality($_POST['personality_text']);
                }
                break;
                
            case 'stop_overthinking':
                $activeTab = 'overthinking';
                $overthinkingResult = getOverthinkingAdvice();
                break;
        }
    }
}
?>
