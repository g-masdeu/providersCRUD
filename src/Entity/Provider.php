<?php

namespace App\Entity;

use App\Repository\ProviderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entidad que representa a un Proveedor de servicios turísticos.
 * * Esta clase mapea la tabla 'provider' en la base de datos y gestiona
 * automáticamente las fechas de creación y actualización mediante Lifecycle Callbacks.
 */
#[ORM\Entity(repositoryClass: ProviderRepository::class)]
#[ORM\HasLifecycleCallbacks] // Necesario para que funcionen PrePersist y PreUpdate
class Provider
{
    /**
     * Identificador único autoincremental.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Nombre comercial del proveedor.
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * Correo electrónico de contacto.
     */
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    /**
     * Teléfono de contacto (guardado como string para soportar prefijos).
     */
    #[ORM\Column(length: 20)]
    private ?string $phone = null;

    /**
     * Clasificación del proveedor (Hotel, Crucero, Esquí, Parque).
     */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type = null;

    /**
     * Estado de disponibilidad del proveedor.
     */
    #[ORM\Column]
    private ?bool $active = null;

    /**
     * Fecha y hora de registro inicial.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Fecha y hora de la última modificación.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    // --- MÉTODOS GETTERS Y SETTERS ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    // --- EVENTOS DE CICLO DE VIDA (Lifecycle Callbacks) ---

    /**
     * Se ejecuta automáticamente justo antes de insertar el registro en la BD.
     * Establece la fecha de creación y la de actualización inicial.
     */
    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Se ejecuta automáticamente antes de cada actualización (UPDATE) en la BD.
     */
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}