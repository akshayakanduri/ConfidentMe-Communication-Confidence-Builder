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

        if (targetSection) targetSection.classList.add('active');
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
});
