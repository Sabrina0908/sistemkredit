<?php

function formatRupiah(int|float $nominal): string
{
    return 'Rp ' . number_format((float) $nominal, 0, ',', '.');
}
