# Documentação de Homologação - Serviço de Cobrança Pix (PagBank)
**Projeto:** Mondy Work (Portal de Vagas)  
**Data:** 27/07/2026  
**Tecnologia Integrada:** API v4 de Pedidos (Orders API) - PagBank / PagSeguro  

---

## 1. Visão Geral da Integração

O serviço de cobrança via Pix do portal **Mondy Work** é integrado à **API v4 de Pedidos (Orders) do PagBank**. O fluxo permite que recrutadores anunciem vagas premium com pagamento instantâneo por QR Code ou chave Copia e Cola.

### Fluxo de Ativação
1. O recrutador preenche o formulário em `post-a-job.php` com os dados da vaga e CPF/CNPJ.
2. O sistema faz uma chamada à API `/orders` do PagBank solicitando uma cobrança Pix.
3. O PagBank retorna a chave Pix Copia e Cola e a imagem do QR Code.
4. O modal de checkout exibe o QR Code com um temporizador de 30 minutos.
5. O sistema realiza *polling* a cada 3 segundos (`/api.php?action=verificar_pagamento`) e aguarda a notificação via Webhook (`/api.php?action=pagbank_webhook`).
6. Assim que o pagamento é confirmado (`PAID`), a vaga é ativada no site por 30 dias com selo **Destaque 🚀** e o recrutador recebe o **Magic Link** de gerenciamento por e-mail.

---

## 2. Estrutura dos Arquivos no Código

| Arquivo | Função / Responsabilidade |
| :--- | :--- |
| `App/Services/PagBankService.php` | Comunicação cURL HTTP com a API PagBank (criação de ordens `/orders` e consultas). |
| `App/Services/VagaPremiumService.php` | Regras de negócio, gravação em banco, ativação de vagas e disparo do Magic Link por e-mail. |
| `api.php` | Centraliza os endpoints: `criar_vaga_premium`, `verificar_pagamento`, `pagbank_webhook` e `simular_pagamento_pix`. |
| `post-a-job.php` | Interface pública de formulário e modal interativo de checkout Pix com polling e timer. |
| `config.php` | Configurações de credenciais (`token`), ambiente (`sandbox` / `production`), preço e validade. |

---

## 3. Endpoints Utilizados

* **Criar Pedido Pix**: `POST /orders`
* **Consultar Pedido**: `GET /orders/{order_id}`
* **Callback / Webhook**: `POST https://mondywork.com.br/api.php?action=pagbank_webhook`

---

## 4. Requests e Responses de Exemplo para Homologação PagBank

### 4.1 Criar Pedido de Cobrança via Pix (`POST /orders`)

#### HTTP Request:
```http
POST /orders HTTP/1.1
Host: sandbox.api.pagseguro.com
Authorization: Bearer <TOKEN_BEARER>
Content-Type: application/json
Accept: application/json

{
  "reference_id": "VAGA_999_1785183957",
  "customer": {
    "name": "Tech Solutions Recrutamento",
    "email": "recrutador@techsolutions.com.br",
    "tax_id": "40075950979"
  },
  "items": [
    {
      "name": "Anúncio Vaga Premium: Desenvolvedor Full Stack Senior (React + Node)",
      "quantity": 1,
      "unit_amount": 4990
    }
  ],
  "qr_codes": [
    {
      "amount": {
        "value": 4990
      },
      "expiration_date": "2026-07-27T17:55:57-03:00"
    }
  ],
  "notification_urls": [
    "https://mondywork.com.br/api.php?action=pagbank_webhook"
  ]
}
```

#### HTTP Response (201 Created):
```json
{
  "id": "ORDE_E9DA4A65-9F27-458B-97C3-62960A303FFC",
  "reference_id": "VAGA_999_1785183957",
  "created_at": "2026-07-27T17:25:57.459-03:00",
  "customer": {
    "name": "Tech Solutions Recrutamento",
    "email": "recrutador@techsolutions.com.br",
    "tax_id": "40075950979"
  },
  "items": [
    {
      "reference_id": "1",
      "name": "Anúncio Vaga Premium: Desenvolvedor Full Stack Senior (React + Node)",
      "quantity": 1,
      "unit_amount": 4990
    }
  ],
  "qr_codes": [
    {
      "id": "QRCO_A9BDCC08-AAFE-407C-ABAF-8AE8D0EA88FA",
      "expiration_date": "2026-07-27T17:55:57.000-03:00",
      "amount": {
        "value": 4990
      },
      "text": "00020101021226850014br.gov.bcb.pix2563api-h.pagseguro.com/pix/v2/A9BDCC08-AAFE-407C-ABAF-8AE8D0EA88FA5204504553039865802BR5915GABRIEL MARQUES6006Canoas62070503***63046A0C",
      "arrangements": [
        "PIX"
      ],
      "links": [
        {
          "rel": "QRCODE.PNG",
          "href": "https://sandbox.api.pagseguro.com/qrcode/QRCO_A9BDCC08-AAFE-407C-ABAF-8AE8D0EA88FA/png",
          "media": "image/png",
          "type": "GET"
        }
      ]
    }
  ],
  "notification_urls": [
    "https://mondywork.com.br/api.php?action=pagbank_webhook"
  ],
  "links": [
    {
      "rel": "SELF",
      "href": "https://sandbox.api.pagseguro.com/orders/ORDE_E9DA4A65-9F27-458B-97C3-62960A303FFC",
      "media": "application/json",
      "type": "GET"
    }
  ]
}
```

---

### 4.2 Consultar Status do Pedido (`GET /orders/{order_id}`)

#### HTTP Request:
```http
GET /orders/ORDE_E9DA4A65-9F27-458B-97C3-62960A303FFC HTTP/1.1
Host: sandbox.api.pagseguro.com
Authorization: Bearer <TOKEN_BEARER>
Accept: application/json
```

#### HTTP Response (200 OK):
```json
{
  "id": "ORDE_E9DA4A65-9F27-458B-97C3-62960A303FFC",
  "reference_id": "VAGA_999_1785183957",
  "created_at": "2026-07-27T17:25:57.459-03:00",
  "customer": {
    "name": "Tech Solutions Recrutamento",
    "email": "recrutador@techsolutions.com.br",
    "tax_id": "40075950979"
  },
  "items": [
    {
      "name": "Anúncio Vaga Premium: Desenvolvedor Full Stack Senior (React + Node)",
      "quantity": 1,
      "unit_amount": 4990
    }
  ],
  "qr_codes": [
    {
      "id": "QRCO_A9BDCC08-AAFE-407C-ABAF-8AE8D0EA88FA",
      "expiration_date": "2026-07-27T17:55:57.000-03:00",
      "amount": {
        "value": 4990
      },
      "text": "00020101021226850014br.gov.bcb.pix2563api-h.pagseguro.com/pix/v2/A9BDCC08-AAFE-407C-ABAF-8AE8D0EA88FA5204504553039865802BR5915GABRIEL MARQUES6006Canoas62070503***63046A0C"
    }
  ],
  "notification_urls": [
    "https://mondywork.com.br/api.php?action=pagbank_webhook"
  ]
}
```

---

### 4.3 Webhook de Notificação PagBank (`POST /api.php?action=pagbank_webhook`)

#### Request Enviado pelo PagBank para Nosso Servidor:
```http
POST /api.php?action=pagbank_webhook HTTP/1.1
Host: mondywork.com.br
Content-Type: application/json

{
  "id": "ORDE_36E8B15F-79A1-42C4-87A5-D6286B1484C9",
  "reference_id": "VAGA_105_1753643200",
  "charges": [
    {
      "id": "CHAR_7B28C3D2-901B-4E32-84B9-8F71E56B12A3",
      "status": "PAID",
      "paid_at": "2026-07-27T16:13:05-03:00",
      "payment_method": {
        "type": "PIX"
      }
    }
  ]
}
```

#### Response Retornado pelo Nosso Servidor (HTTP 200 OK):
```json
{
  "status": "ok"
}
```

---

## 5. Passos para Homologação e Virada de Chave (Go-Live)

1. **Desenvolvimento / Teste Simulado**:
   - Sem token configurado no `config.php`, o sistema gera QR Codes simulados e disponibiliza o botão `⚡ Teste Sandbox: Simular Pagamento Confirmado` no modal de checkout.
2. **Ambiente Sandbox (PagBank Connect)**:
   - Configure em `config.php`:
     ```php
     'pagbank' => [
         'token' => 'SEU_TOKEN_SANDBOX',
         'env'   => 'sandbox'
     ]
     ```
3. **Produção**:
   - Insira o token de produção e altere o ambiente:
     ```php
     'pagbank' => [
         'token' => 'SEU_TOKEN_PRODUCAO',
         'env'   => 'production'
     ]
     ```
   - Verifique o suporte a SSL (`HTTPS`) no domínio para recebimento dos Webhooks.
