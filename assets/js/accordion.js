let questions = document.querySelectorAll(".faq_question");

questions.forEach((question) => {
    let container = question.closest(".faq_container");
    let icon = question.querySelector(".icon-shape");

    question.addEventListener("click", (event) => {
        const active = document.querySelector(".faq_container.active");
        const activeIcon = active ? active.querySelector(".icon-shape.active") : null;

        if (active && active !== container) {
            active.classList.toggle("active");
            if (activeIcon) {
                activeIcon.classList.toggle("active");
            }
            active.querySelector(".answercont").style.maxHeight = 0;
        }

        container.classList.toggle("active");
        icon.classList.toggle("active");

        const answer = container.querySelector(".answercont");

        if (container.classList.contains("active")) {
            answer.style.maxHeight = answer.scrollHeight + "px";
        } else {
            answer.style.maxHeight = 0;
        }
    });
});

// Add resize function
window.addEventListener("resize", () => {
    const activeContainers = document.querySelectorAll(".faq_container.active");

    activeContainers.forEach((activeContainer) => {
        const answer = activeContainer.querySelector(".answercont");
        answer.style.maxHeight = answer.scrollHeight + "px";
    });
});

// Toggle enable-border class based on admin settings
let enableBorder = "<?php echo get_option('faq_accordion_enable_border', 'no'); ?>";

if (enableBorder === 'yes') {
    document.querySelectorAll('.faq_container').forEach(container => {
        container.classList.add('enable-border');
    });
}
