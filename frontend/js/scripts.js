document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const benefitList = document.getElementById('benefitList');
    const filters = document.querySelectorAll('.filter');

    searchInput.addEventListener('input', function() {
        const searchText = searchInput.value.toLowerCase();
        const benefits = benefitList.querySelectorAll('li');

        benefits.forEach(function(benefit) {
            const benefitText = benefit.textContent.toLowerCase();
            if (benefitText.includes(searchText)) {
                benefit.style.display = '';
            } else {
                benefit.style.display = 'none';
            }
        });
    });

    filters.forEach(function(filter) {
        filter.addEventListener('change', function() {
            const selectedFilters = Array.from(filters).filter(f => f.checked).map(f => f.value);
            const benefits = benefitList.querySelectorAll('li');

            benefits.forEach(function(benefit) {
                const benefitCategories = benefit.dataset.categories.split(',');
                const benefitCity = benefit.dataset.city;
                const benefitPoints = parseInt(benefit.dataset.points, 10);

                const matchesCategory = selectedFilters.some(filter => benefitCategories.includes(filter));
                const matchesCity
                document.addEventListener('DOMContentLoaded', function() {
                    const searchInput = document.getElementById('searchInput');
                    const benefitList = document.getElementById('benefitList');
                    const filters = document.querySelectorAll('.filter');
                
                    searchInput.addEventListener('input', function() {
                        const searchText = searchInput.value.toLowerCase();
                        const benefits = benefitList.querySelectorAll('li');
                
                        benefits.forEach(function(benefit) {
                            const benefitText = benefit.textContent.toLowerCase();
                            if (benefitText.includes(searchText)) {
                                benefit.style.display = '';
                            } else {
                                benefit.style.display = 'none';
                            }
                        });
                    });
                
                    filters.forEach(function(filter) {
                        filter.addEventListener('change', function() {
                            const selectedFilters = Array.from(filters).filter(f => f.checked).map(f => f.value);
                            const benefits = benefitList.querySelectorAll('li');
                
                            benefits.forEach(function(benefit) {
                                const benefitCategories = benefit.dataset.categories.split(',');
                                const benefitCity = benefit.dataset.city;
                                const benefitPoints = parseInt(benefit.dataset.points, 10);
                
                                const matchesCategory = selectedFilters.some(filter => benefitCategories.includes(filter));
                                const matchesCity = selectedFilters.includes(benefitCity);
                                const matchesPoints = selectedFilters.some(filter => benefitPoints <= parseInt(filter, 10));
                
                                if (matchesCategory || matchesCity || matchesPoints) {
                                    benefit.style.display = '';
                                } else {
                                    benefit.style.display = 'none';
                                }
                            });
                        });
                    });
                });
            });
        });
    });
});