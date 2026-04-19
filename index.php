<?php
session_start();
require_once __DIR__ . '/logic.php';
$darkModeClass = $_SESSION['dark_mode'] ?? '' ? 'dark-mode' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ConfidentMe - Communication Builder</title>
    <!-- Google Fonts for the script logo and modern body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?= $darkModeClass ?>">
    
    <!-- Top Header & Navigation -->
    <header class="site-header">
        <div class="logo-container">
            <h1 class="logo">ConfidentMe</h1>
        </div>
        
        <nav class="main-nav">
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <ul class="nav-links" id="nav-links">
                <li><a href="#" class="nav-item" data-target="mood">MOOD TOOLBOX</a></li>
                <li><a href="#" class="nav-item" data-target="challenge">DAILY CHALLENGE</a></li>
                <li><a href="#" class="nav-item" data-target="practice">COMMUNICATION</a></li>
                <li><a href="#" class="nav-item" data-target="video">VIDEO PRACTICE</a></li>
                <li><a href="#" class="nav-item" data-target="personality">PERSONALITY SWITCH</a></li>
                <li><a href="#" class="nav-item" data-target="overthinking">RESCUE</a></li>
                <li>
                    <form method="POST" style="margin: 0; display: inline;">
                        <input type="hidden" name="action" value="toggle_dark_mode">
                        <button type="submit" class="btn-dark-toggle">
                            <?= $_SESSION['dark_mode'] ? 'LIGHT' : 'DARK' ?>
                        </button>
                    </form>
                </li>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h2>Join ConfidentMe for your Communication & Confidence Experience</h2>
            <button class="hero-btn" onclick="document.getElementById('dashboard').scrollIntoView({behavior: 'smooth'})">The Confidence Space Dashboard</button>
        </div>
    </section>

    <!-- Main Dashboard Application -->
    <div class="container main-content" id="dashboard">
        <div class="stats-bar">
            <div class="stat-badge">🔥 Streak: <?= $_SESSION['streak'] ?></div>
            <div class="stat-badge">⭐ Score: <?= $_SESSION['score'] ?></div>
            <div class="stat-badge">✅ Tasks: <?= $_SESSION['tasks_completed'] ?></div>
        </div>

        <div class="sections-wrapper">
            
            <!-- FEATURE 1 & 2: Mood Toolbox -->
            <section id="mood" class="content-section active">
                <div class="card">
                    <h2 class="section-title">Mood Toolbox</h2>
                    <p class="subtitle">Acknowledge your feelings to move forward.</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="check_mood">
                        <div class="form-group">
                            <input type="text" name="mood_text" class="modern-input" placeholder="e.g. I feel nervous to speak in class" required>
                        </div>
                        <button type="submit" class="btn hero-btn">Analyze Mood</button>
                    </form>

                    <?php if ($moodResult): ?>
                    <div class="result-box slide-up">
                        <div class="result-header">
                            <span class="emotion-badge"><?= htmlspecialchars($moodResult->emotion) ?></span>
                        </div>
                        <p class="explanation"><strong>Why:</strong> <?= htmlspecialchars($moodResult->explanation) ?></p>
                        
                        <div class="actionable-steps">
                            <p><strong>Actionable Solutions:</strong></p>
                            <ul class="actions-list">
                                <?php foreach($moodResult->actions->action as $action): ?>
                                    <li><?= htmlspecialchars($action) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <p class="quote">"<?= htmlspecialchars($moodResult->quote) ?>"</p>
                        
                        <div class="suggestion-box">
                            <strong>💡 Suggestion:</strong> <?= htmlspecialchars($moodResult->actionSuggestion) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- FEATURE 5: Daily Confidence Challenge -->
            <section id="challenge" class="content-section">
                <div class="card text-center">
                    <h2 class="section-title">Daily Challenge</h2>
                    <p class="subtitle">Push your boundaries one step at a time.</p>
                    <div class="challenge-box">
                        <h3><?= htmlspecialchars($dailyChallenge) ?></h3>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="complete_challenge">
                        <button type="submit" class="btn hero-btn btn-large pulse-hover">Mark as Done</button>
                    </form>
                </div>
            </section>

            <!-- FEATURE 3: Language & Communication Practice -->
            <section id="practice" class="content-section">
                <div class="card">
                    <h2 class="section-title">Communication Practice</h2>
                    <p class="subtitle">Practice makes perfect. Simulate real-world scenarios.</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="evaluate_speech">
                        <div class="scenario-header">
                            <span class="scenario-badge">Scenario: Job Interview</span>
                            <p class="prompt-text">Prompt: Introduce yourself confidently.</p>
                        </div>
                        <div class="form-group">
                            <textarea name="speech_text" class="modern-textarea" rows="5" placeholder="Type your response here..." required></textarea>
                        </div>
                        <button type="submit" class="btn outline-btn">Evaluate Response</button>
                    </form>

                    <?php if ($speechResult): ?>
                    <div class="result-box slide-up">
                        <div class="score-header">
                            <h3>Confidence Score: <?= $speechResult['score'] ?>%</h3>
                            <?php 
                                $badgeClass = $speechResult['score'] >= 70 ? 'badge-high' : ($speechResult['score'] >= 40 ? 'badge-medium' : 'badge-low');
                                $badgeText = $speechResult['score'] >= 70 ? 'High' : ($speechResult['score'] >= 40 ? 'Medium' : 'Needs Work');
                            ?>
                            <span class="score-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $speechResult['score'] ?>%;"></div>
                        </div>
                        <div class="feedback-section">
                            <p><strong>Feedback:</strong></p>
                            <ul class="actions-list">
                                <?php foreach($speechResult['feedback'] as $fb): ?>
                                    <li><?= htmlspecialchars($fb) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- FEATURE 4: AI Video Practice Generator -->
            <section id="video" class="content-section">
                <div class="card">
                    <h2 class="section-title">Video Simulator</h2>
                    <p class="subtitle">Look at the camera and speak confidently.</p>
                    <div class="video-container">
                        <video id="live-video" autoplay muted class="preview-video"></video>
                        <video id="playback-video" controls class="preview-video" style="display: none;"></video>
                    </div>
                    
                    <div class="video-controls">
                        <button type="button" id="start-record-btn" class="btn outline-btn">Start Recording</button>
                        <button type="button" id="stop-record-btn" class="btn btn-danger" style="display: none;">Stop Recording</button>
                        <span id="recording-status" class="recording-status" style="display: none;">Recording...</span>
                    </div>

                    <div class="scenario-details">
                        <div class="scenario-header">
                            <span class="scenario-badge">Scenario: Talking to a Stranger</span>
                        </div>
                        <p class="instructions"><strong>Instructions:</strong> Speak your answer out loud while looking at the camera.</p>
                        <div class="sample-response">
                            <strong>Sample Confident Response:</strong><br>
                            <span class="italic-text">"Hi there! I couldn't help but notice your book. Is it a good read?"</span>
                        </div>
                    </div>
                    
                    <form method="POST" class="mt-20">
                        <input type="hidden" name="action" value="mark_video_done">
                        <button type="submit" class="btn hero-btn">Mark Video as Done</button>
                    </form>
                </div>
            </section>

            <!-- FEATURE 6: Personality Switch Tool -->
            <section id="personality" class="content-section">
                <div class="card">
                    <h2 class="section-title">Personality Switch</h2>
                    <p class="subtitle">See how phrasing changes the perception of your message.</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="switch_personality">
                        <div class="form-group">
                            <input type="text" name="personality_text" class="modern-input" placeholder="e.g. I think I want this job" required>
                        </div>
                        <button type="submit" class="btn hero-btn">Transform Message</button>
                    </form>

                    <?php if ($personalityResult): ?>
                    <div class="personality-results-grid slide-up">
                        <div class="persona-box shy-box">
                            <div class="persona-title">Shy</div>
                            <p><?= htmlspecialchars($personalityResult['shy']) ?></p>
                        </div>
                        <div class="persona-box normal-box">
                            <div class="persona-title">Normal</div>
                            <p><?= htmlspecialchars($personalityResult['normal']) ?></p>
                        </div>
                        <div class="persona-box confident-box">
                            <div class="persona-title">Confident</div>
                            <p><?= htmlspecialchars($personalityResult['confident']) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- EXTRA FEATURES -->
            <section id="overthinking" class="content-section">
                <div class="card calm-card">
                    <h2 class="section-title">Overthinking Rescue</h2>
                    <p class="subtitle">Feeling overwhelmed? Take a deep breath and center yourself.</p>
                    <form method="POST" class="center-form">
                        <input type="hidden" name="action" value="stop_overthinking">
                        <button type="submit" class="btn outline-btn btn-large pulse-hover">Stop Overthinking</button>
                    </form>

                    <?php if ($overthinkingResult): ?>
                    <div class="quote-box slide-up">
                        <p class="calm-quote">✨ <?= htmlspecialchars($overthinkingResult) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

        </div>
    </div>

    <!-- Pass PHP active tab to JS -->
    <script>
        const initialTab = "<?= $activeTab ?? 'mood' ?>";
    </script>
    <script src="app.js"></script>
    <script>
        // MediaRecorder Logic for Video Simulator
        let mediaRecorder;
        let recordedChunks = [];
        const liveVideo = document.getElementById('live-video');
        const playbackVideo = document.getElementById('playback-video');
        const startBtn = document.getElementById('start-record-btn');
        const stopBtn = document.getElementById('stop-record-btn');
        const recordingStatus = document.getElementById('recording-status');

        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                .then(stream => {
                    liveVideo.srcObject = stream;
                    
                    mediaRecorder = new MediaRecorder(stream);
                    
                    mediaRecorder.ondataavailable = function(e) {
                        if (e.data.size > 0) {
                            recordedChunks.push(e.data);
                        }
                    };
                    
                    mediaRecorder.onstop = function() {
                        const blob = new Blob(recordedChunks, { type: 'video/webm' });
                        recordedChunks = [];
                        const videoURL = URL.createObjectURL(blob);
                        playbackVideo.src = videoURL;
                        playbackVideo.style.display = 'block';
                        liveVideo.style.display = 'none';
                    };
                })
                .catch(err => {
                    console.error("Error accessing camera: ", err);
                    startBtn.disabled = true;
                    startBtn.textContent = "Camera Denied";
                });
        }

        if(startBtn) {
            startBtn.addEventListener('click', () => {
                if (mediaRecorder && mediaRecorder.state === 'inactive') {
                    mediaRecorder.start();
                    startBtn.style.display = 'none';
                    stopBtn.style.display = 'inline-flex';
                    playbackVideo.style.display = 'none';
                    liveVideo.style.display = 'block';
                    recordingStatus.style.display = 'inline-block';
                    recordingStatus.classList.add('blink');
                }
            });
        }

        if(stopBtn) {
            stopBtn.addEventListener('click', () => {
                if (mediaRecorder && mediaRecorder.state === 'recording') {
                    mediaRecorder.stop();
                    stopBtn.style.display = 'none';
                    startBtn.style.display = 'inline-flex';
                    startBtn.textContent = "Re-Record";
                    recordingStatus.style.display = 'none';
                    recordingStatus.classList.remove('blink');
                }
            });
        }
    </script>
</body>
</html>
