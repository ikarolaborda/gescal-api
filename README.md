# GESCAL API - Sistema de Gestão Social e Calamidades

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-blue.svg)](https://php.net)
[![JSON:API](https://img.shields.io/badge/JSON%3AAPI-1.1-green.svg)](https://jsonapi.org/)
[![License](https://img.shields.io/badge/license-Proprietary-yellow.svg)]()

## 📋 Visão Geral

Sistema de apoio à gestão socioassistencial e de calamidades, desenvolvido com foco em conformidade com LGPD e boas práticas de desenvolvimento. O sistema oferece:

- ✅ **Cadastro de pessoas e famílias** com PII protegida
- ✅ **Gestão de casos de atendimento** a desastres e calamidades
- ✅ **Programas de benefícios sociais** e acompanhamento
- ✅ **API REST JSON:API compliant** com versionamento
- ✅ **Autenticação JWT** com controle de acesso baseado em roles
- ✅ **Notificações por email** para coordenadores
- ✅ **Operações em massa** (import/export)
- ✅ **Compliance LGPD** com exportação de dados e retenção configurável

---

## 🚀 Quick Start

### Pré-requisitos

- Docker & Docker Compose
- Make (opcional, mas recomendado)

### Instalação com Docker

```bash
# Clone o repositório
git clone <repository-url>
cd gescal-api

# Configure o ambiente
cp .env.example .env
# Edite .env com suas configurações

# Inicie os containers
make up
# ou: docker-compose up -d

# Instale dependências e rode migrações
make install
# ou: docker-compose exec app composer install
#      docker-compose exec app php artisan migrate

# Crie dados de teste
make seed
# ou: docker-compose exec app php artisan db:seed
```

A API estará disponível em: `http://localhost:8000`

### Primeiro Acesso

```bash
# Criar usuário administrador
docker-compose exec app php artisan tinker
>>> $user = User::factory()->create(['email' => 'admin@gescal.gov.br']);
>>> $user->roles()->attach(Role::where('slug', 'admin')->first());
>>> exit

# Obter token JWT
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/vnd.api+json" \
  -d '{"email":"admin@gescal.gov.br","password":"password"}'
```

---

## 🏗️ Stack Tecnológica

### Backend (API)

- **Laravel 12.x** - Framework PHP moderno
- **PHP 8.4** - Última versão estável
- **JSON:API 1.1** - Especificação REST completa
- **JWT Auth** - Autenticação stateless com tymon/jwt-auth
- **MySQL 8.0** - Banco de dados relacional
- **Redis 7** - Cache e filas
- **Laravel Horizon** - Monitor de filas
- **Laravel Telescope** - Debug e monitoramento
- **Mailhog** - Teste de emails (desenvolvimento)

### Infraestrutura

- **Docker** - Containerização
- **Nginx + PHP-FPM** - Web server
- **Supervisor** - Gerenciamento de processos
- **Laravel Pint** - Code formatting (PSR-12)
- **PHPUnit** - Testes automatizados
- **Larastan** - Análise estática (PHPStan)

### Frontend (Separado)

- **Vue 3** (Composition API)
- **Pinia** - Gerenciamento de estado
- **Comunicação via JSON:API**

---

## 📚 API Documentation

### Autenticação

Todas as requisições (exceto `/auth/login` e dados de referência) requerem autenticação JWT.

```bash
# Login
POST /api/v1/auth/login
Content-Type: application/vnd.api+json

{
  "email": "user@example.com",
  "password": "password"
}

# Response
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhb...",
  "token_type": "bearer",
  "expires_in": 3600,
  "roles": ["coordinator"]
}

# Use o token nas requisições
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhb...
```

### Principais Endpoints

#### Pessoas (Persons)
- `GET /api/v1/persons` - Listar pessoas (com filtros, paginação)
- `GET /api/v1/persons/{id}` - Obter pessoa específica
- `POST /api/v1/persons` - Criar pessoa
- `PATCH /api/v1/persons/{id}` - Atualizar pessoa
- `DELETE /api/v1/persons/{id}` - Soft delete pessoa
- `GET /api/v1/persons/{id}/data-export` - Exportar dados (LGPD Art. 18)

#### Famílias (Families)
- `GET /api/v1/families` - Listar famílias
- `GET /api/v1/families/{id}` - Obter família específica
- `POST /api/v1/families` - Criar família
- `PATCH /api/v1/families/{id}` - Atualizar família
- `DELETE /api/v1/families/{id}` - Soft delete família

#### Casos (Cases)
- `GET /api/v1/cases` - Listar casos de atendimento
- `GET /api/v1/cases/{id}` - Obter caso específico
- `POST /api/v1/cases` - Criar caso

#### Benefícios (Benefits)
- `GET /api/v1/benefits` - Listar benefícios
- `GET /api/v1/benefits/{id}` - Obter benefício específico
- `POST /api/v1/benefits` - Criar benefício

#### Dados de Referência (Público)
- `GET /api/v1/reference-data/federation-units` - UFs brasileiras
- `GET /api/v1/reference-data/race-ethnicities` - Raças/Etnias
- `GET /api/v1/reference-data/marital-statuses` - Estados civis
- `GET /api/v1/reference-data/benefit-programs` - Programas de benefícios

#### Operações em Massa (Coordinator/Admin)
- `POST /api/v1/bulk/import` - Importar múltiplos recursos
- `POST /api/v1/bulk/export` - Exportar múltiplos recursos

### Exemplos de Uso

#### Criar uma Pessoa

```bash
POST /api/v1/persons
Authorization: Bearer {token}
Content-Type: application/vnd.api+json
Accept: application/vnd.api+json

{
  "data": {
    "type": "persons",
    "attributes": {
      "full_name": "João da Silva",
      "sex": "Masculino",
      "birth_date": "1985-03-15",
      "nationality": "brasileiro",
      "natural_city": "São Paulo",
      "primary_phone": "(11) 98765-4321",
      "email": "joao.silva@example.com"
    },
    "relationships": {
      "naturalFederationUnit": {
        "data": { "type": "federation-units", "id": "1" }
      },
      "raceEthnicity": {
        "data": { "type": "race-ethnicities", "id": "1" }
      },
      "maritalStatus": {
        "data": { "type": "marital-statuses", "id": "1" }
      },
      "schoolingLevel": {
        "data": { "type": "schooling-levels", "id": "1" }
      }
    }
  }
}
```

#### Listar Pessoas com Filtros

```bash
GET /api/v1/persons?filter[full_name]=Silva&sort=-created_at&page[number]=1&page[size]=25&include=documents,families
Authorization: Bearer {token}
Accept: application/vnd.api+json
```

#### Exportação em Massa

```bash
POST /api/v1/bulk/export
Authorization: Bearer {token}
Content-Type: application/vnd.api+json

{
  "types": ["people", "families"],
  "filters": {
    "created_since": "2025-01-01"
  }
}
```

### Versionamento da API

A API suporta versionamento via URL:

- **V1 (Atual)**: `/api/v1/*` - Versão estável e ativa
- **V2 (Futuro)**: `/api/v2/*` - Planejada para futuras melhorias

Headers de versão:
```
X-API-Version: 1.0
X-API-Deprecated: false
```

Para mais detalhes, consulte: [`docs/api-versioning.md`](docs/api-versioning.md)

### Especificação OpenAPI

A especificação completa está disponível em:
- **Arquivo**: `specs/002-jsonapi-rest-api/contracts/openapi.yaml`
- **Swagger UI**: `http://localhost:8000/api/documentation` (quando configurado)

---

## Arquitetura de Camadas

### 1. Requests (Form Requests / DTOs)

- Validação de **forma e tipo de dados** vindos da API:
  - Campos obrigatórios (`required`)
  - Tipos (`string`, `date`, `array`, etc.)
  - Formatos (`email`, `date_format`, etc.)
  - Limites (`max`, `min`)
- Mensagens de erro voltadas à interface/usuário.

### 2. Actions (Action Pattern / Services)

- Onde mora a **regra de aplicação**, especialmente quando envolve:
  - Múltiplas entidades (ex.: criar família + responsável + membros)
  - Operações transacionais (tudo ou nada)
  - Orquestração de chamadas de repositórios/modelos
- Exemplos:
  - `CreateFamilyAction`
  - `RegisterOccurrenceAndCaseAction`
  - `GrantBenefitForCalamityAction`

### 3. Models (Eloquent)

- Refletem **a estrutura de dados** e **regras de negócio de 3º nível**, ou seja:
  - Invariantes que **nunca** devem ser quebradas, independentemente da camada de entrada.
  - Ex.: “Uma família não pode ter mais de um responsável na pivot `family_person`.”
- Uso de:
  - `protected $guarded = [];` (sem `$fillable`)
  - Método `casts()` do Laravel 12 para tipagem de atributos
  - Trait de validação de modelo (`HasModelValidation`) para garantir invariantes no `creating`/`updating`.

---

## Modelo de Dados (Visão Conceitual)

### Usuários e Infra

- **users**
  - Usuários da aplicação (autenticação).
- **password_reset_tokens**
  - Tokens para recuperação de senha.
- **sessions**
  - Sessões (se usada session-based auth).
- **jobs / job_batches / failed_jobs**
  - Tabelas padrão de fila de jobs (mesmo com Redis como backend).

---

### Catálogos / Tabelas Auxiliares

#### `federation_units`

- Representa UF brasileiras:
  - Coluna `federation_unit` (`AC`, `AL`, ..., `TO`, `nao_declarado`)
- Usada em:
  - `addresses.state_id` (UF do endereço)
  - `people.natural_federation_unit_id` (naturalidade)
  - `families.origin_federation_unit_id` (UF de origem da família)
  - `documents.issuing_federation_unit_id` (UF do órgão expedidor)

#### `race_ethnicities`

- Coluna `race_color` (enum):
  - `branca`, `preta`, `parda`, `amarela`, `indigena`, `nao_declarada`
- Associada a `people.race_ethnicity_id`.

#### `marital_statuses`

- Coluna `marital_status` (enum):
  - `solteiro`, `casado`, `divorciado`, `viuvo`, `união_estável`, `nao_declarado`
- Associada a `people.marital_status_id`.

#### `schooling_levels`

- Coluna `schooling_level` (enum):
  - `fundamental_incompleto`, `fundamental_completo`,  
    `medio_incompleto`, `medio_completo`,  
    `superior_incompleto`, `superior_completo`,  
    `pos_graduacao_incompleto`, `pos_graduacao_completo`,  
    `nao_declarado`
- Associada a `people.schooling_level_id`.

#### `kinships`

- Coluna `kinship` (enum):
  - Parentescos: `pai`, `mae`, `filho`, `filha`, `irmao`, `irma`, `avô`, `avó`, `tio`, `tia`,  
    `sobrinho`, `sobrinha`, `primo`, `prima`, `sogro`, `sogra`, `genro`, `esposa`, `marido`,  
    `filho_adotivo`, `filha_adotiva`, `neto`, `neta`, `bisneto`, `bisneta`, `nao_declarado`, `outro`.
- Usada na pivot `family_person.kinship_id`.

#### `document_types`

- Coluna `document_type` (enum):
  - `cpf`, `cnpj`, `rg`, `cnh`, `passaporte`, `outro`, `nao_declarado`.
- Usada em `documents.document_type_id`.

#### `occurrence_types`

- Coluna `occurrence_type` (enum):
  - Ex.: `incendio`, `deslizamento`, `inundação`, `risco_de_incendio`, `risco_de_deslizamento`,  
    `risco_de_inundação`, `desabamento`, `destelhamento`, `queda_de_muro`,  
    `queda_de_arvore`, `queda_de_edificio`, `queda_de_ponte`, `enxurrada`, `terremoto`,  
    `outro`, `nao_declarado`.
- Relacionada com `occurrences.occurrence_type_id`.

#### `benefit_programs`

- **Tabela flexível** de programas de benefício.
- Migrou de enum fixo para um modelo flexível (ou está planejado):
  - Ex.: colunas `code` (slug) e `name` (descritivo).
- Ligada a `benefits.benefit_program_id`.

---

### Endereços

#### `addresses`

- Representa endereços físicos:
  - `street`, `number`, `complement`, `neighborhood`, `city`, `zip_code`, `reference_point`
  - `state_id` → `federation_units.id`
- Associada a:
  - `families.address_id` (endereço atual da família)

---

### Pessoas e Documentos

#### `people`

- Pessoa física (indivíduo):
  - `full_name`
  - `sex` (`Masculino`, `Feminino`)
  - `birth_date`
  - `filiation_text` (texto livre de filiação como no documento)
  - `nationality` (default `brasileiro`)
  - `natural_city`, `natural_federation_unit_id`
  - FKs para `race_ethnicity_id`, `marital_status_id`, `schooling_level_id`
  - Contatos: `primary_phone`, `secondary_phone`, `email`
- Usada como:
  - Responsável familiar (`families.responsible_person_id`)
  - Beneficiário de benefícios (`benefits.person_id`)
  - Portador de documentos (`documents.person_id`)

#### `documents`

- Documentos de uma pessoa:
  - `person_id` → `people.id`
  - `document_type_id` → `document_types.id`
  - `number`
  - `issuing_body`, `issuing_federation_unit_id`, `issued_at`
  - `is_primary` (true/false)
- Possíveis regras de negócio futuras:
  - Uma pessoa não pode ter mais de um documento primário do mesmo tipo.

---

### Famílias e Vínculos

#### `families`

- Unidade familiar:
  - `responsible_person_id` → `people.id` (responsável oficial)
  - `address_id` → `addresses.id` (endereço da família)
  - `origin_city`, `origin_federation_unit_id`
  - `family_income_bracket`, `family_income_value`
- Relacionada com:
  - `housing_units` (unidades habitacionais)
  - `benefits` (benefícios ligados à família)
  - `cases` (casos / atendimentos)

#### `family_person` (pivot)

- Tabela de relação família–pessoa:
  - `family_id` → `families.id`
  - `person_id` → `people.id`
  - `kinship_id` → `kinships.id`
  - `is_responsible` (bool)
  - `lives_in_household` (bool)
- Regra de negócio típica (a ser aplicada em Model/Action):
  - **Uma família não deve ter mais de um membro marcado como `is_responsible = true`.**

---

### Ocorrências e Calamidades

#### `occurrences`

- Representam ocorrências de calamidade / risco:
  - `number` (string, único)
  - `year`
  - `occurrence_type_id` → `occurrence_types.id`
  - `summary` (texto livre)
- Podem estar associadas a vários **casos** (`cases.occurrence_id`).

---

### Moradia / Habitação

#### `housing_units`

- Unidades habitacionais ligadas a uma família:
  - `family_id` → `families.id`
  - `housing_situation` (enum: `PROPRIA`, `ALUGADA`, `CEDIDA`, `OCCUPIED`, `OTHER`)
  - `construction_type` (enum: `ALVENARIA`, `MADEIRA`, `MISTA`, `OTHER`)
  - `room_count`
  - `rent_or_financing_value`
  - `participates_housing_program`, `housing_program_name`, `housing_program_process`
  - `length_of_residence_months`
- Também associadas a **casos** (`cases.housing_unit_id`) como “foto” da moradia naquele atendimento.

---

### Benefícios

#### `benefits`

- Benefícios concedidos a famílias e/ou pessoas:
  - `family_id` → `families.id` (opcional)
  - `person_id` → `people.id` (opcional, ex.: BPC para uma pessoa específica)
  - `benefit_program_id` → `benefit_programs.id`
  - `value`
  - `is_active`
  - `started_at`, `ended_at`
- Podem estar associados a **casos** via pivot `case_benefits`.

---

### Casos e Relatórios Sociais

#### `cases` (model `CaseRecord`)

- Representam um **atendimento / caso socioassistencial**:
  - `family_id` → `families.id`
  - `occurrence_id` → `occurrences.id` (pode ser nulo)
  - `housing_unit_id` → `housing_units.id` (pode ser nulo)
  - `dc_number` (único), `dc_year`
  - `service_date`
  - `notes` (anotações gerais)
- Relacionamentos:
  - Muitos-para-muitos com `benefits` via `case_benefits`
  - Um-para-muitos com `case_social_reports`

#### `case_benefits`

- Pivot entre `cases` e `benefits`:
  - `case_id` → `cases.id`
  - `benefit_id` → `benefits.id`
- Garante quais benefícios foram concedidos/relacionados a determinado caso.

#### `case_social_reports`

- Relatos sociais vinculados a um caso:
  - `case_id` → `cases.id`
  - `report_text` (LONGTEXT)
- Permite múltiplos relatórios por caso, com `created_at` e `updated_at` para histórico.

---

## Modelos Eloquent — Padrões Gerais

Em todos os modelos de domínio:

- Uso de `protected $guarded = [];` (sem `$fillable`).
- Uso de `protected function casts(): array` para:
  - Datas (`date:Y-m-d`, `datetime`)
  - Booleanos (`boolean`)
  - Decimais (`decimal:2`)
  - Inteiros (`integer`)
- Relacionamentos nomeados de forma semântica:
  - `Person::naturalFederationUnit()`
  - `Document::issuingFederationUnit()`
  - `Family::originFederationUnit()`
  - `CaseRecord::family()`, `CaseRecord::occurrence()`, `CaseRecord::housingUnit()`, etc.

### Trait de Validação de Modelo

Há uma trait opcional `HasModelValidation` que:

- Registra callbacks no `creating` e `updating`.
- Procura por um método `rules()` no modelo.
- Executa `Validator::make($this->attributesToArray(), $this->rules())` e lança `ValidationException` se necessário.

A ideia é usar isso **apenas** para invariantes de domínio importantes, não para duplicar validação básica de Request.

Exemplos de uso:

- Em `FamilyPerson`: garantir no `creating/updating` que só exista **um responsável** por família.
- Em `Benefit`: garantir que `ended_at` ≥ `started_at`.
- Em `CaseRecord`: garantir que `service_date` seja uma data válida e, eventualmente, não futura, dependendo da regra de negócio.

---

## Serialização e JSON:API

- A serialização básica é fornecida por Eloquent, considerando `casts` e `hidden`.
- Para seguir JSON:API à risca, recomenda-se:
  - Criar **API Resources** (`PersonResource`, `FamilyResource`, `CaseResource`, etc.)
  - Nessas Resources, estruturar explicitamente saída no formato:

    ```json
    {
      "data": {
        "type": "people",
        "id": "1",
        "attributes": {
          "full_name": "...",
          "sex": "Masculino",
          ...
        },
        "relationships": {
          "family": {
            "data": { "type": "families", "id": "1" }
          }
        }
      }
    }
    ```

- As rotas podem ser organizadas por recurso (`/api/people`, `/api/families`, `/api/cases`, `/api/occurrences`), respeitando:
  - Métodos HTTP (GET/POST/PATCH/DELETE)
  - Convenções de inclusão de relações (`include=family,benefits`), filtros, paginação etc., conforme a especificação JSON:API.

---

## Fluxo de Desenvolvimento

### Requisitos

- PHP compatível com Laravel 12.x
- Banco MySQL/MariaDB
- Redis
- Node.js + npm/yarn para o frontend Vue 3

### Passos iniciais (backend)

1. Clonar repositório da API.
2. Criar `.env` baseado em `.env.example`.
3. Configurar:
   - DB (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)
   - Redis (`REDIS_HOST`, `REDIS_PASSWORD` se houver)
4. Rodar migrations:

   ```bash
   php artisan migrate
