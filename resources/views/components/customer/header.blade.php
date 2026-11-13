 <header
     class="flex flex-wrap items-center justify-between gap-4 border-b border-stone-200 bg-white px-5 py-4 sm:px-8 lg:px-6">
     <div>
         <h1 class="mt-1 text-2xl font-black text-stone-900 sm:text-3xl"> {{ $title }} </h1>
     </div>
     <button id="cartButton" type="button"
         class="relative flex shrink-0 items-center gap-2 rounded-xl bg-orange-500 px-3 py-3 text-sm font-bold text-white transition hover:bg-orange-600 sm:px-4"
         aria-label="Buka keranjang">
         <span class="text-lg">🛒</span>
         <span class="hidden sm:inline">Keranjang</span>

         <span id="cartCount"
             class="grid h-5 min-w-5 place-items-center rounded-full bg-white px-1 text-[10px] font-black text-orange-600">
             0
         </span>
     </button>
 </header>
