@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'field-shell shadow-sm']) }}>
