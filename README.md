# Sistema de Gestão Social e Calamidades

## Visão Geral

Este projeto é um sistema de apoio à gestão socioassistencial e de calamidades, com foco em:

- **Cadastro de pessoas e famílias**
- **Acompanhamento de ocorrências (desastres / riscos)**
- **Condições de moradia (unidades habitacionais)**
- **Benefícios e programas sociais (incluindo benefícios associados a calamidades)**
- **Registros de casos / atendimentos e respectivos relatórios sociais**

A modelagem de dados foi inspirada em fichas sociais padronizadas (ex.: Ficha Social padrão 2023) e nas diretrizes do SUAS, com preocupação em:

- Normalização forte (pessoas, famílias, endereços, benefícios, etc.)
- Separação clara entre **família**, **pessoa**, **ocorrência** e **caso**
- Flexibilidade em alguns cadastros (ex.: tipos de benefícios)

---

## Stack Tecnológica

### Backend (API)

- **Laravel 12.x**
- API **stateless** seguindo convenções **JSON:API**:
  - `type`, `id`, `attributes`, `relationships`
  - Respostas JSON padronizadas para facilitar o consumo por SPAs e outros clientes
- **MySQL** / MariaDB como banco relacional
- **Redis** para:
  - Fila de jobs (`jobs`, `job_batches`, `failed_jobs` podem ser usados conforme configuração)
  - Cache de aplicação (opcional)
- Autenticação baseada no modelo `User` padrão do Laravel (podendo evoluir para Sanctum / JWT conforme necessidade)

### Frontend

- **Vue 3** (Composition API)
- **Pinia** para gerenciamento de estado
- SPA rodando em **origem distinta** da API (CORS habilitado na API)
- Comunicação exclusivamente via JSON:API

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
