<?php

namespace App\Entity;

use App\Repository\CartRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=CartRepository::class)
 */
class Cart
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=CartProduct::class, mappedBy="cart", orphanRemoval=true)
     */
    private $cartProducts;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function __construct()
    {
        $this->cartProducts = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|CartProduct[]
     */
    public function getCartProducts(): Collection
    {
        return $this->cartProducts;
    }

    public function addProduct(CartProduct $product): self
    {
        if (!$this->cartProducts->contains($product)) {
            $this->cartProducts[] = $product;
            $product->setCart($this);
        }

        return $this;
    }

    public function removeProduct(CartProduct $product): self
    {
        if ($this->cartProducts->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCart() === $this) {
                $product->setCart(null);
            }
        }

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTotalProductInCart(): int
    {
        return count($this->getCartProducts());
    }

    public function getTotalProductAmount(): array
    {
        $total = [];
        /** @var CartProduct $cartProduct */
        foreach ($this->cartProducts as $cartProduct) {
            $currencyCode = $cartProduct->getProduct()->getCurrency()->getId();
            if (!array_key_exists($currencyCode, $total)) {
                $total[$currencyCode] = 0.0;
            }
            $amount = $cartProduct->getProduct()->getPrice() * $cartProduct->getQuantity();
            $total[$currencyCode] = round(($total[$currencyCode] + $amount), 2);
        }

        return $total;
    }
}
