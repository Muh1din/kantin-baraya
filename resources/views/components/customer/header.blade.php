 <header
     class="flex flex-wrap items-center justify-between gap-4 border-b border-stone-200 bg-white px-5 py-4 sm:px-8 lg:px-6">
     <div>
         <h1 class="mt-1 text-2xl font-black text-stone-900 sm:text-3xl"> {{ $title }} </h1>
     </div>
     @if (request()->routeIs('customer.menus.index'))
         <button id="cartButton" type="button"
             class="relative flex shrink-0 items-center gap-2 rounded-xl bg-orange-500 px-4 py-3 text-sm font-bold text-white transition hover:bg-orange-600"
             aria-label="Buka keranjang" aria-controls="cartDrawer" aria-expanded="false">
             <span class="text-lg" aria-hidden="true">🛒</span>
             <span>Keranjang</span>
         </button>
     @endif
 </header>
