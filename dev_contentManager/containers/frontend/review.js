import { removeNodes } from "@ECMUtils/helper";

export const manageReviewSelection = (reviewDropdown) => {
    const reviewTriggerText = reviewDropdown.querySelector('.ecm-shortcode--review__dropdown-trigger-text');
    const reviewOptions = reviewDropdown.querySelectorAll('.ecm-shortcode--review__option-item');
    const reviewToggle = document.querySelector('.ecm-shortcode--review__dropdown-state');
    const ratingInput = document.getElementById('ecm-review-rating-input');

    removeNodes(reviewDropdown);

    reviewOptions.forEach(option => {
        option.addEventListener('click', (e) => {
            reviewTriggerText.innerHTML = option.innerHTML;
            const ratingVal = option.getAttribute('data-value');
            const fallbackVal = option.querySelectorAll('svg').length;
            ratingInput.value = ratingVal || fallbackVal;
        });
    });

    document.addEventListener('click', (e) => {
        if (!reviewDropdown.contains(e.target) && reviewToggle.checked) {
            reviewToggle.checked = false;
        }
    });
}

export const manageReviewStarsUI = (ratingStars) => {
    ratingStars.forEach((star) => {
        removeNodes(star);
        
        star.addEventListener('click', function () {
            const rating = this.getAttribute('data-rating');

            ratingStars.forEach(s => {
                if (Number(s.getAttribute('data-rating')) <= Number(rating)) {
                    s.classList.add('is-active');
                } else {
                    s.classList.remove('is-active');
                }
            });
        });

        star.addEventListener('mouseenter', function () {
            const rating = this.getAttribute('data-rating');

            ratingStars.forEach(s => {
                if (Number(s.getAttribute('data-rating')) <= Number(rating)) {
                    s.classList.add('is-hovered');
                } else {
                    s.classList.remove('is-hovered');
                }
            });
        });

        star.addEventListener('mouseleave', function () {
            ratingStars.forEach(s => s.classList.remove('is-hovered'));
        });
    });
}