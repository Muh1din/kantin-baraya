function initializeMenuFilters() {
    const menuSection = document.getElementById('menu');
    const categories = document.getElementById('categories');
    const noMenu = document.getElementById('noMenu');
    const searchInput = document.getElementById('menuSearch');
    const sortSelect = document.getElementById('sortMenu');

    if (!menuSection || !categories || !noMenu) return;

    const categoryButtons = Array.from(categories.querySelectorAll('.category-btn'));
    const menuGrid = menuSection.querySelector('.product-card')?.parentElement;
    if (!menuGrid) return;

    const productCards = Array.from(menuGrid.querySelectorAll('.product-card'));
    const originalOrder = new Map(productCards.map((card, index) => [card, index]));
    let activeCategory = 'all';

    function updateActiveCategory(selectedButton) {
        categoryButtons.forEach((button) => {
            const isActive = button === selectedButton;
            button.classList.toggle('active', isActive);
            button.classList.toggle('border-orange-200', isActive);
            button.classList.toggle('bg-orange-500', isActive);
            button.classList.toggle('text-white', isActive);
            button.classList.toggle('border-stone-200', !isActive);
            button.classList.toggle('bg-white', !isActive);
            button.setAttribute('aria-pressed', String(isActive));
        });
    }

    function sortMenus() {
        const sortBy = sortSelect?.value ?? 'featured';

        productCards.sort((first, second) => {
            if (sortBy === 'low' || sortBy === 'high') {
                const difference = Number(first.dataset.price) - Number(second.dataset.price);
                return sortBy === 'low' ? difference : -difference;
            }

            if (sortBy === 'name') {
                return (first.dataset.menuName ?? '').localeCompare(
                    second.dataset.menuName ?? '',
                    'id',
                    { sensitivity: 'base' },
                );
            }

            const featuredDifference =
                Number(second.dataset.featured ?? 0) - Number(first.dataset.featured ?? 0);
            return featuredDifference || originalOrder.get(first) - originalOrder.get(second);
        });

        productCards.forEach((card) => menuGrid.appendChild(card));
    }

    function filterMenus() {
        const searchTerm = (searchInput?.value ?? '').trim().toLocaleLowerCase('id-ID');
        let visibleCount = 0;

        productCards.forEach((card) => {
            const matchesCategory =
                activeCategory === 'all' || card.dataset.category === activeCategory;
            const matchesSearch = (card.dataset.menuName ?? '')
                .toLocaleLowerCase('id-ID')
                .includes(searchTerm);
            const isVisible = matchesCategory && matchesSearch;

            card.classList.toggle('hidden', !isVisible);
            if (isVisible) visibleCount++;
        });

        noMenu.classList.toggle('hidden', visibleCount > 0);
    }

    categoryButtons.forEach((button) => {
        button.addEventListener('click', () => {
            activeCategory = button.dataset.category ?? 'all';
            updateActiveCategory(button);
            filterMenus();
        });
    });

    searchInput?.addEventListener('input', filterMenus);
    sortSelect?.addEventListener('change', () => {
        sortMenus();
        filterMenus();
    });

    sortMenus();
    filterMenus();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMenuFilters, { once: true });
} else {
    initializeMenuFilters();
}
