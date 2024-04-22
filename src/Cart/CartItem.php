<?php

namespace App\Cart;

use App\Entity\Book;

class CartItem
{
    public $book;
    public $qty;

    public function __construct(Book $book, int $qty)
    {
        $this->book = $book;
        $this->qty = $qty;
    }
}
