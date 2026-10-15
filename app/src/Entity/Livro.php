<?php

namespace App\Entity;

use App\Repository\LivroRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LivroRepository::class)]
#[ORM\Table(name: 'Livro')]
#[UniqueEntity(fields: ['titulo'], message: 'Já existe um livro cadastrado com esse título.')]
class Livro
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'CodI', type: 'integer')]
    private ?int $codI = null;

    #[ORM\Column(name: 'Titulo', length: 40)]
    #[Assert\NotBlank(message: 'Informe o título do livro.')]
    #[Assert\Length(max: 40, maxMessage: 'O título pode ter no máximo {{ limit }} caracteres.')]
    private ?string $titulo = null;

    #[ORM\Column(name: 'Editora', length: 40)]
    #[Assert\NotBlank(message: 'Informe a editora.')]
    #[Assert\Length(max: 40, maxMessage: 'A editora pode ter no máximo {{ limit }} caracteres.')]
    private ?string $editora = null;

    #[ORM\Column(name: 'Edicao', type: 'integer')]
    #[Assert\NotNull(message: 'Informe a edição.')]
    #[Assert\Positive(message: 'A edição deve ser um número positivo.')]
    private ?int $edicao = null;

    #[ORM\Column(name: 'AnoPublicacao', length: 4)]
    #[Assert\NotBlank(message: 'Informe o ano de publicação.')]
    #[Assert\Regex(pattern: '/^\d{4}$/', message: 'Informe um ano com 4 dígitos (ex.: 2024).')]
    private ?string $anoPublicacao = null;

    #[ORM\Column(name: 'Valor', type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Informe o valor do livro.')]
    #[Assert\PositiveOrZero(message: 'O valor não pode ser negativo.')]
    private ?string $valor = null;

    /**
     * @var Collection<int, Autor>
     */
    #[ORM\ManyToMany(targetEntity: Autor::class, inversedBy: 'livros')]
    #[ORM\JoinTable(name: 'Livro_Autor')]
    #[ORM\JoinColumn(name: 'Livro_CodI', referencedColumnName: 'CodI')]
    #[ORM\InverseJoinColumn(name: 'Autor_CodAu', referencedColumnName: 'CodAu')]
    #[Assert\Count(min: 1, minMessage: 'Selecione pelo menos um autor.')]
    private Collection $autores;

    /**
     * @var Collection<int, Assunto>
     */
    #[ORM\ManyToMany(targetEntity: Assunto::class, inversedBy: 'livros')]
    #[ORM\JoinTable(name: 'Livro_Assunto')]
    #[ORM\JoinColumn(name: 'Livro_CodI', referencedColumnName: 'CodI')]
    #[ORM\InverseJoinColumn(name: 'Assunto_codAs', referencedColumnName: 'codAs')]
    private Collection $assuntos;

    public function __construct()
    {
        $this->autores = new ArrayCollection();
        $this->assuntos = new ArrayCollection();
    }

    public function getCodI(): ?int
    {
        return $this->codI;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getEditora(): ?string
    {
        return $this->editora;
    }

    public function setEditora(string $editora): static
    {
        $this->editora = $editora;

        return $this;
    }

    public function getEdicao(): ?int
    {
        return $this->edicao;
    }

    public function setEdicao(int $edicao): static
    {
        $this->edicao = $edicao;

        return $this;
    }

    public function getAnoPublicacao(): ?string
    {
        return $this->anoPublicacao;
    }

    public function setAnoPublicacao(string $anoPublicacao): static
    {
        $this->anoPublicacao = $anoPublicacao;

        return $this;
    }

    public function getValor(): ?string
    {
        return $this->valor;
    }

    public function setValor(string $valor): static
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * @return Collection<int, Autor>
     */
    public function getAutores(): Collection
    {
        return $this->autores;
    }

    public function addAutor(Autor $autor): static
    {
        if (!$this->autores->contains($autor)) {
            $this->autores->add($autor);
            $autor->addLivro($this);
        }

        return $this;
    }

    public function removeAutor(Autor $autor): static
    {
        if ($this->autores->removeElement($autor)) {
            $autor->removeLivro($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Assunto>
     */
    public function getAssuntos(): Collection
    {
        return $this->assuntos;
    }

    public function addAssunto(Assunto $assunto): static
    {
        if (!$this->assuntos->contains($assunto)) {
            $this->assuntos->add($assunto);
            $assunto->addLivro($this);
        }

        return $this;
    }

    public function removeAssunto(Assunto $assunto): static
    {
        if ($this->assuntos->removeElement($assunto)) {
            $assunto->removeLivro($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->titulo ?? '';
    }
}
