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
    $text = trim($text);
    if (empty($text)) {
        return ['score' => 0, 'feedback' => ["Please enter a response."]];
    }

    $wordCount = str_word_count($text);
    $score = 0;
    $feedback = [];
    
    // Strict Length Conditions
    if ($wordCount < 10) {
        return ['score' => 10, 'feedback' => ["Your response is too short. A proper introduction requires more detail."]];
    } elseif ($wordCount >= 10 && $wordCount < 30) {
        $score += 15;
        $feedback[] = "You gave a basic response, but an introduction should be more elaborate.";
    } elseif ($wordCount >= 30 && $wordCount <= 100) {
        $score += 30;
        $feedback[] = "Good length. You provided enough detail for a solid introduction.";
    } else {
        $score += 20;
        $feedback[] = "Your response is quite long. Try to be more concise in an introduction.";
    }
    
    // Strict Keyword Conditions
    $confidentWords = ['confident', 'believe', 'sure', 'definitely', 'excited', 'passionate', 'experience', 'can', 'will', 'ready', 'strong', 'always', 'know', 'lead', 'achieve', 'success', 'skills', 'background'];
    $hesitantWords = ['maybe', 'um', 'uh', 'sorry', 'guess', 'think', 'probably', 'like', 'just', 'kinda', 'sorta', 'perhaps', 'basically'];
    
    // Check for self-introduction markers
    $introMarkers = ['i am', "i'm", 'my name', 'background', 'experience', 'worked as', 'graduated', 'student', 'career', 'skills'];
    $hasIntro = false;
    $lowerText = strtolower($text);
    foreach ($introMarkers as $marker) {
        if (strpos($lowerText, $marker) !== false) {
            $hasIntro = true;
            break;
        }
    }
    
    if ($hasIntro) {
        $score += 20;
        $feedback[] = "Good job including standard introduction elements.";
    } else {
        $feedback[] = "Make sure to clearly introduce who you are (e.g., 'My name is...', 'I am...').";
    }

    $confidentCount = 0;
    $hesitantCount = 0;
    
    // Use unique words to prevent spamming
    $words = str_word_count($lowerText, 1);
    $uniqueWords = array_unique($words);
    
    foreach ($uniqueWords as $word) {
        if (in_array($word, $confidentWords)) $confidentCount++;
        if (in_array($word, $hesitantWords)) $hesitantCount++;
    }
    
    // Score based on confident words
    if ($confidentCount >= 4) {
        $score += 30;
        $feedback[] = "Excellent use of diverse, positive language!";
    } elseif ($confidentCount >= 2) {
        $score += 20;
        $feedback[] = "Good use of positive language, but you could show even more conviction.";
    } elseif ($confidentCount == 1) {
        $score += 10;
        $feedback[] = "You used a confident word, but try to project more certainty overall.";
    } else {
        $feedback[] = "Try incorporating more strong, confident words (e.g., 'believe', 'definitely', 'will').";
    }
    
    // Penalize hesitant words heavily
    if ($hesitantCount > 2) {
        $score -= 20;
        $feedback[] = "You used several hesitant filler words. This undermines your confidence.";
    } elseif ($hesitantCount > 0) {
        $score -= 10;
        $feedback[] = "Avoid using filler words like 'maybe' or 'um'. Speak with absolute certainty.";
    } else {
        $score += 20;
        $feedback[] = "Great job avoiding hesitant filler words!";
    }
    
    // Repetition penalty
    $repetitionRatio = count($uniqueWords) / max(1, $wordCount);
    if ($repetitionRatio < 0.4 && $wordCount > 20) {
        $score -= 15;
        $feedback[] = "Your response is somewhat repetitive. Try to vary your vocabulary.";
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
    $text = trim($text);
    if (empty($text)) {
         return [
             'shy' => '...',
             'normal' => '',
             'confident' => 'Let\'s get started!'
         ];
    }

    $shyResult = "";
    $confidentResult = "";
    $matchedPattern = false;

    // 1. Structural Regex Patterns for natural sentence rewriting
    $patterns = [
        [
            'regex' => '/^i want (.*)/i',
            'shy' => 'I was wondering if it might be possible to get $1, if that\'s okay...',
            'confident' => 'I am determined to get $1.'
        ],
        [
            'regex' => '/^i need (.*)/i',
            'shy' => 'I might need a little help getting $1...',
            'confident' => 'I require $1.'
        ],
        [
            'regex' => '/^(?:can|could|may) i buy (.*)/i',
            'shy' => 'I was wondering if it might be possible for me to buy $1...',
            'confident' => 'I am ready to move forward with buying $1.'
        ],
        [
            'regex' => '/^(?:can|could|may) i (.*)/i',
            'shy' => 'I was wondering if it might be possible for me to $1...',
            'confident' => 'I am fully prepared to $1.'
        ],
        [
            'regex' => '/^(?:can|could|would) you (.*)/i',
            'shy' => 'I\'m sorry to bother you, but would you be able to $1?',
            'confident' => 'Please $1.'
        ],
        [
            'regex' => '/^how do i (.*)/i',
            'shy' => 'I\'m so sorry to ask, but would you happen to know how I might $1?',
            'confident' => 'Please instruct me on how to $1.'
        ],
        [
            'regex' => '/^why did you (.*)/i',
            'shy' => 'I was just wondering why you $1, if you don\'t mind me asking...',
            'confident' => 'Please explain your reasoning for $1.'
        ],
        [
            'regex' => '/^i (?:think|believe|guess|feel like) (.*)/i',
            'shy' => 'It\'s just my opinion, but maybe $1...',
            'confident' => 'I am completely confident that $1.'
        ],
        [
            'regex' => '/^i don\'t know (.*)/i',
            'shy' => 'I\'m really not sure about $1...',
            'confident' => 'I will investigate $1 immediately.'
        ],
        [
            'regex' => '/^(?:i\'m )?sorry (?:for|about) (.*)/i',
            'shy' => 'I am so, so sorry about $1. Please forgive me...',
            'confident' => 'Thank you for your patience regarding $1.'
        ],
        [
            'regex' => '/^this is (.*)/i',
            'shy' => 'I could be wrong, but this seems like $1...',
            'confident' => 'This is clearly $1.'
        ],
        [
            'regex' => '/^let\'s (.*)/i',
            'shy' => 'Maybe we could consider $1, if everyone agrees...',
            'confident' => 'I strongly recommend we $1.'
        ],
        [
            'regex' => '/^we should (.*)/i',
            'shy' => 'Perhaps we could try to $1...',
            'confident' => 'I propose we $1.'
        ],
        [
            'regex' => '/^(?:tell|give|show) me (.*)/i',
            'shy' => 'If it\'s not too much trouble, could you share $1 with me?',
            'confident' => 'Please provide me with $1.'
        ],
        [
            'regex' => '/^i am (.*)/i',
            'shy' => 'I kind of feel like I might be $1...',
            'confident' => 'I am undeniably $1.'
        ]
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern['regex'], $text)) {
            $shyResult = preg_replace($pattern['regex'], $pattern['shy'], $text);
            $confidentResult = preg_replace($pattern['regex'], $pattern['confident'], $text);
            $matchedPattern = true;
            break;
        }
    }

    // 2. Fallback: Word/Phrase Level Replacement if no full sentence structure matched
    if (!$matchedPattern) {
        $genericShy = [
            '/\b(we should)\b/i' => 'maybe we could try to',
            '/\b(i will)\b/i' => 'i might be able to',
            '/\b(yes)\b/i' => 'i guess so',
            '/\b(no)\b/i' => 'i don\'t think so',
            '/\b(i hate)\b/i' => 'i\'m not the biggest fan of',
            '/\b(want)\b/i' => 'would kinda like',
            '/\b(need)\b/i' => 'might need',
        ];
        
        $genericConfident = [
            '/\b(maybe|perhaps|possibly)\b/i' => 'definitely',
            '/\b(i guess|i think)\b/i' => 'i know',
            '/\b(i hope)\b/i' => 'i expect',
            '/\b(sorry|excuse me)\b/i' => 'thank you for understanding',
            '/\b(just|kinda|sorta)\b/i' => '',
            '/\b(try to)\b/i' => 'will',
            '/\b(want)\b/i' => 'intend to',
        ];

        $shyResult = preg_replace(array_keys($genericShy), array_values($genericShy), $text);
        $confidentResult = preg_replace(array_keys($genericConfident), array_values($genericConfident), $text);

        // If it STILL didn't change much, we apply a contextual wrapper
        if (strcasecmp($shyResult, $text) === 0) {
            $shyPrefixes = ['Um, ', 'I was just thinking, ', 'Perhaps ', 'Maybe '];
            $shyResult = $shyPrefixes[array_rand($shyPrefixes)] . lcfirst($text);
        }
        
        if (strcasecmp($confidentResult, $text) === 0) {
            $cleanText = rtrim($text, ".!?");
            if (preg_match('/\?$/', $text)) {
                // Leave questions mostly alone but ensure capitalization
                $confidentResult = ucfirst($text);
            } else {
                $confidentResult = 'To be clear, ' . lcfirst($cleanText) . '.';
            }
        }
    }

    // Final Polish: Clean up spaces and punctuation
    $shyResult = trim(preg_replace('/\s+/', ' ', $shyResult));
    $confidentResult = trim(preg_replace('/\s+/', ' ', $confidentResult));

    // Fix trailing punctuation issues from regex replacements
    $shyResult = rtrim($shyResult, "?"); // Shy responses rarely sound like confident questions
    if (!preg_match('/[.!?]$/', $shyResult) && !preg_match('/\.\.\.$/', $shyResult)) {
        $shyResult .= '...';
    }
    
    // Confident statements often replace requests, so change trailing ? to .
    if (preg_match('/^(Please|I require|I would like|I recommend|I propose)/i', $confidentResult)) {
        $confidentResult = rtrim($confidentResult, "?") . '.';
    } elseif (!preg_match('/[.!?]$/', $confidentResult)) {
        $confidentResult .= '.';
    }

    return [
        'shy' => ucfirst($shyResult),
        'normal' => ucfirst($text),
        'confident' => ucfirst($confidentResult)
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
