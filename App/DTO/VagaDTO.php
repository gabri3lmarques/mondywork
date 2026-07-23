<?php

namespace App\DTO;

class VagaDTO
{
    public function __construct(
        public string $vagaIdExterno,
        public string $titulo,
        public string $empresa,
        public ?string $localizacao = null,
        public ?string $modeloTrabalho = null,
        public ?string $urlVaga = null,
        public string $descricao = '',
        public string $resumo = '',
        public ?string $publicadoEm = null,
        public string $status = 'inativa',
        public string $origem = 'nacional',
        public ?int $id = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            vagaIdExterno: $data['vaga_id_externo'] ?? $data['vagaIdExterno'] ?? '',
            titulo:        $data['titulo'] ?? '',
            empresa:       $data['empresa'] ?? '',
            localizacao:   $data['localizacao'] ?? null,
            modeloTrabalho:$data['modelo_trabalho'] ?? $data['modeloTrabalho'] ?? null,
            urlVaga:       $data['url_vaga'] ?? $data['urlVaga'] ?? null,
            descricao:     $data['descricao'] ?? '',
            resumo:        $data['resumo'] ?? '',
            publicadoEm:   $data['publicado_em'] ?? $data['publicadoEm'] ?? null,
            status:        $data['status'] ?? 'inativa',
            origem:        $data['origem'] ?? 'nacional',
            id:            isset($data['id']) ? (int)$data['id'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'vaga_id_externo' => $this->vagaIdExterno,
            'titulo'          => $this->titulo,
            'empresa'         => $this->empresa,
            'localizacao'     => $this->localizacao,
            'modelo_trabalho' => $this->modeloTrabalho,
            'url_vaga'        => $this->urlVaga,
            'descricao'       => $this->descricao,
            'resumo'          => $this->resumo,
            'publicado_em'    => $this->publicadoEm,
            'status'          => $this->status,
            'origem'          => $this->origem,
        ];
    }
}
