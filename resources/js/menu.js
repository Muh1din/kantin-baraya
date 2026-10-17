

const menuItems = window.menuItems || [

    {
        id: 1,
        name: 'Nasi Goreng',
        category: 'makanan',
        price: 15000,
        description: 'Nasi goreng gurih dan lezat.',
        image: '',
    },
    {
        id: 2,
        name: 'Mie Goreng',
        category: 'makanan',
        price: 12000,
        description: 'Mie goreng dengan bumbu pilihan.',
        image: '',
    },
    {
        id: 3,
        name: 'Ayam Geprek',
        category: 'makanan',
        price: 18000,
        description: 'Ayam crispy dengan sambal pedas.',
        image: '',
    },
    {
        id: 4,
        name: 'Es Teh Manis',
        category: 'minuman',
        price: 5000,
        description: 'Teh manis segar dengan es.',
        image: '',
    },
    {
        id: 5,
        name: 'Es Jeruk',
        category: 'minuman',
        price: 7000,
        description: 'Minuman jeruk yang menyegarkan.',
        image: '',
    },
    {
        id: 6,
        name: 'Kentang Goreng',
        category: 'snack',
        price: 10000,
        description: 'Kentang goreng renyah.',
        image: '',
    },
];

document.addEventListener('DOMContentLoaded', () => {
    const menuGrid = document.getElementById('menuGrid');
    const menuSearch = document.getElementById('menuSearch');
    const sortMenu = document.getElementById('sortMenu');
    const resultCount = document.getElementById('resultCount');
    const noMenu = document.getElementById('noMenu');
    const resetFilters = document.getElementById('resetFilters');
    const categoryButtons = document.querySelectorAll('[data-category]');

    // Pastikan elemen menu tersedia di halaman.
    if (!menuGrid) return;

    let activeCategory = 'all';

    const formatRupiah = (price) =>
        new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(price);

    function renderMenu() {
        const keyword = menuSearch?.value.trim().toLowerCase() || '';
        const sortValue = sortMenu?.value || '';

        let filteredItems = menuItems.filter((item) => {
            const matchesKeyword =
                item.name.toLowerCase().includes(keyword) ||
                item.description.toLowerCase().includes(keyword);

            const matchesCategory =
                activeCategory === 'all' ||
                item.category === activeCategory;

            return matchesKeyword && matchesCategory;
        });

        if (sortValue === 'low') {
    filteredItems.sort((a, b) => a.price - b.price);
} else if (sortValue === 'high') {
    filteredItems.sort((a, b) => b.price - a.price);
}

        menuGrid.innerHTML = filteredItems
            .map(
                (item) => `
                    <article class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                        <div class="flex h-32 items-center justify-center bg-orange-50 text-5xl">
                            ${
                                item.image
                                    ? `<img src="${item.image}" alt="${item.name}" class="h-full w-full object-cover">`
                                    : item.category === 'minuman'
                                      ? '🥤'
                                      : item.category === 'snack'
                                        ? '🍟'
                                        : '🍛'
                            }
                        </div>

                        <div class="p-3">
                            
<h3 class="font-semibold text-gray-800">
    ${item.name}
</h3>

<p class="mt-1 text-xs ${
    item.is_available && item.stock > 0
        ? 'text-green-600'
        : 'text-red-600'
}">
    ${
        item.is_available && item.stock > 0
            ? `Stok tersedia: ${item.stock}`
            : 'Menu habis'
    }
</p>

                        </div>
                    </article>
                `,
            )
            .join('');

        if (resultCount) {
            resultCount.textContent =
                `${filteredItems.length} menu ditemukan`;
        }

        if (noMenu) {
            noMenu.classList.toggle('hidden', filteredItems.length > 0);
        }

        menuGrid.classList.toggle('hidden', filteredItems.length === 0);
    }

    menuSearch?.addEventListener('input', renderMenu);
    sortMenu?.addEventListener('change', renderMenu);

    categoryButtons.forEach((button) => {
        button.addEventListener('click', () => {
            activeCategory = button.dataset.category || 'all';
            renderMenu();
        });
    });

    resetFilters?.addEventListener('click', () => {
        if (menuSearch) menuSearch.value = '';
        if (sortMenu) sortMenu.value = '';

        activeCategory = 'all';
        renderMenu();
    });

    renderMenu();
});
