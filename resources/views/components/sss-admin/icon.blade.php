@props(['name' => 'dashboard'])
<svg {{ $attributes->class(['icon']) }} viewBox="0 0 24 24" aria-hidden="true" focusable="false">
@switch($name)
@case('dashboard')
<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
@break
@case('products')
<path d="m8 3-5 4 3 5 2-1v10h8V11l2 1 3-5-5-4c0 4-8 4-8 0Z"/>
@break
@case('orders')
<path d="M5 7h14l1 14H4L5 7Z"/><path d="M8 8V6a4 4 0 018 0v2"/>
@break
@case('customers')
<circle cx="9" cy="7" r="3"/><path d="M3 21v-3a6 6 0 0112 0v3M16 4a3 3 0 010 6M18 14c3 0 4 3 4 7"/>
@break
@case('categories')
<path d="M3 7V4h7l3 3h8v13H3V7Z"/>
@break
@case('settings')
<path d="M4 6h16M4 12h16M4 18h16"/><circle cx="8" cy="6" r="2"/><circle cx="16" cy="12" r="2"/><circle cx="10" cy="18" r="2"/>
@break
@case('arrow')
<path d="M5 19 19 5M5 5h14v14"/>
@break
@case('menu')
<path d="M4 6h16M4 12h16M4 18h16"/>
@break
@case('search')
<circle cx="10" cy="10" r="6"/><path d="m15 15 6 6"/>
@break
@case('plus')
<path d="M12 4v16M4 12h16"/>
@break
@case('download')
<path d="M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5"/>
@break
@default
<circle cx="12" cy="12" r="8"/>
@endswitch
</svg>
