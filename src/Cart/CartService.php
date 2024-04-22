<?php

namespace App\Cart;

use App\Cart\CartItem;
use App\Repository\BookRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    protected $requestStack;
    protected $bookRepository;

    public function __construct(RequestStack $requestStack, BookRepository $bookRepository)
    {
        $this->requestStack = $requestStack;
        $this->bookRepository = $bookRepository;
    }

    protected function getCart(): array
    {
        $session = $this->requestStack->getSession();
        return $session->get('cart', []);
    }

    protected function saveCart(array $cart)
    {
        $session = $this->requestStack->getSession();
        $session->set('cart', $cart);
    }

    public function empty()
    {
        $this->saveCart([]);
    }

    public function add(int $id): bool
    {
        // 1. Retrouver le panier dans la session (sous forme de tableau)
        // 2. S'il n'existe pas encore, alors prendre un tableau vide
        $cart = $this->getCart();

        // 3. Voir si le livre {id} existe déjà dans le tableau
        // 4. Si c'est le cas, simplement passer la quantité à 1

        // Refactoring
        $isNewBorrowing = false;
        if (!array_key_exists($id, $cart)) {
            $cart[$id] = 0;
            $isNewBorrowing = true;
        }

        $cart[$id] = 1;

        // 6. Enregistrer le tableau mis à jour dans la session
        $this->saveCart($cart);

        // Retourner true si c'est une nouvel emprunt, sinon false
        return $isNewBorrowing;
    }

    public function remove(int $id)
    {
        $cart = $this->getCart();

        unset($cart[$id]);
        $this->saveCart($cart);
    }

    public function getTotal(): int
    {
        $total = 0;

        foreach ($this->getCart() as $id => $qty) {
            $book = $this->bookRepository->find($id);

            if (!$book) {
                continue;
            }

            $total += $qty;
        }

        return $total;
    }

    /**
     * @return CartItem[]
     */
    public function getDetailedCartItems(): array
    {
        // $session = $this->requestStack->getSession();
        // $detailedCart = [];
        // foreach ($session->get('cart', []) as $id => $qty) {
        //     $book = $this->bookRepository->find($id);
        //     $detailedCart[] = [
        //         'book' => $book,
        //         'qty' => $qty
        //     ];
        // }
        // return $detailedCart;
        $detailedCart = [];

        foreach ($this->getCart() as $id => $qty) {
            $book = $this->bookRepository->find($id);

            if (!$book) {
                continue;
            }

            $detailedCart[] = new CartItem($book, $qty);
        }
        return $detailedCart;
    }
}
