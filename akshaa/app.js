document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.nav-item');
    const sections = document.querySelectorAll('.content-section');
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('nav-links');

    // Toggle Mobile Menu
    if (hamburger) {
        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            hamburger.classList.toggle('open');
        });
    }

    // Function to switch active section
    function switchSection(targetId) {
        // Remove active class from all sections
        sections.forEach(sec => sec.classList.remove('active'));
        // Remove active class from all links
        navLinks.forEach(link => link.classList.remove('active'));

        // Find the target section and link
        const targetSection = document.getElementById(targetId);
        const targetLink = document.querySelector(`.nav-item[data-target="${targetId}"]`);

        if (targetSection) {
            targetSection.classList.add('active');
            // Give browser a moment to render before scrolling
            setTimeout(() => {
                targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 50);
        }
        
        if (targetLink) targetLink.classList.add('active');

        // Close mobile menu if open
        if (navMenu.classList.contains('active')) {
            navMenu.classList.remove('active');
            hamburger.classList.remove('open');
        }
    }

    // Add click listeners to all nav links
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('data-target');
            switchSection(targetId);
        });
    });

    // Initialize with the tab from PHP or fallback
    if (typeof initialTab !== 'undefined') {
        switchSection(initialTab);
    } else {
        switchSection('mood');
    }

    // Grounding Exercise Logic
    const startGroundingBtn = document.getElementById('start-grounding-btn');
    const nextGroundingBtn = document.getElementById('next-grounding-btn');
    const groundingIntro = document.getElementById('grounding-intro');
    const groundingExercise = document.getElementById('grounding-exercise');
    const groundingText = document.getElementById('grounding-text');
    const breathingCircle = document.getElementById('breathing-circle');

    if (startGroundingBtn && groundingExercise) {
        const groundingSteps = [
            { text: "Take a deep breath in...", action: 'inhale' },
            { text: "And slowly exhale...", action: 'exhale' },
            { text: "Name 5 things you can see.", action: 'inhale' },
            { text: "Name 4 things you can touch.", action: 'exhale' },
            { text: "Name 3 things you can hear.", action: 'inhale' },
            { text: "Name 2 things you can smell.", action: 'exhale' },
            { text: "Name 1 thing you can taste.", action: 'inhale' },
            { text: "Take one final deep breath in...", action: 'inhale' },
            { text: "And release everything.", action: 'exhale' },
            { text: "✨ You are calm and grounded.", action: 'finish' }
        ];

        let currentStepIndex = 0;

        function playGroundingStep() {
            if (currentStepIndex >= groundingSteps.length) {
                // If we're past the last step, reset the exercise completely
                groundingExercise.style.display = 'none';
                groundingIntro.style.display = 'flex';
                currentStepIndex = 0;
                return;
            }

            const step = groundingSteps[currentStepIndex];
            
            groundingText.style.opacity = 0; // Fade out
            
            setTimeout(() => {
                groundingText.textContent = step.text;
                groundingText.style.opacity = 1; // Fade in
                
                if (step.action === 'inhale') {
                    breathingCircle.classList.add('inhale');
                    breathingCircle.classList.remove('exhale');
                } else if (step.action === 'exhale') {
                    breathingCircle.classList.add('exhale');
                    breathingCircle.classList.remove('inhale');
                } else {
                    breathingCircle.classList.remove('inhale', 'exhale');
                }

                // Update button text on the final step
                if (currentStepIndex === groundingSteps.length - 1) {
                    nextGroundingBtn.textContent = "Finish";
                } else {
                    nextGroundingBtn.textContent = "Next Step";
                }
            }, 500);
        }

        startGroundingBtn.addEventListener('click', (e) => {
            e.preventDefault();
            groundingIntro.style.display = 'none';
            groundingExercise.style.display = 'block';
            nextGroundingBtn.style.display = 'inline-block';
            nextGroundingBtn.textContent = "Next Step";
            currentStepIndex = 0;
            playGroundingStep();
        });

        nextGroundingBtn.addEventListener('click', (e) => {
            e.preventDefault();
            currentStepIndex++;
            playGroundingStep();
        });
    }
});
