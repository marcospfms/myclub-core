# Personas de Usuário — MyClub

## 1. Filosofia de papéis

No MyClub, um usuário não possui um "tipo de conta" fixo. Os papéis emergem de forma incremental conforme o uso da plataforma:

- todo acesso começa com um `user` (conta base)
- ao preencher dados esportivos, o usuário ganha um perfil de `player`
- ao criar um time, o usuário se torna `owner` desse time
- ao ser adicionado à comissão técnica de um time, o usuário ganha um perfil de `staff_member`

Esses papéis **não são excludentes** e se acumulam livremente. O cenário mais comum no futebol amador — o responsável pelo time que também joga nele — é suportado naturalmente pelo modelo.

---

## 2. Como os perfis se acumulam

| Estado do usuário | Tabelas envolvidas |
| --- | --- |
| Conta criada | `users` |
| Perfil de jogador preenchido | `users` + `players` |
| Time criado | `users` + `teams` (`owner_id`) |
| Adicionado à comissão técnica | `users` + `staff_members` + `team_staff` |
| Jogador que também criou time | `users` + `players` + `teams` (`owner_id`) + `player_memberships` |
| Todos os papéis ao mesmo tempo | `users` + `players` + `teams` + `staff_members` |

A lógica técnica por trás desse design está detalhada em `docs/database/schema.md` — seção 1 (Identidade e Usuários).

---

## 3. Personas principais

### 3.1 Jogador Puro

Usuário que se cadastrou, preencheu seus dados esportivos e participa de times como atleta. Não gerencia nenhum time.

**Perfil:**
- Frequentemente adicionado ao elenco por um gestor de time
- Quer ver seu histórico de jogos, estatísticas e badges
- Pode tornar seu perfil descobrível para que times o encontrem por posição e localização
- Pode assinar o **Player Pro** para ter destaque e visibilidade pública ampliada

**O que vê na plataforma:**
- Seu perfil de jogador (posição, dados pessoais, histórico)
- Times dos quais faz parte
- Partidas agendadas e resultados
- Seus badges conquistados
- Ranking de times dos campeonatos em que participa
- Convites de times para entrar no elenco

**Não gerencia:**
- Elencos
- Campeonatos
- Configurações de time

---

### 3.2 Dono de Time

Usuário que criou um ou mais times. Responsável pela gestão do elenco, amistosos e inscrição em campeonatos.

**Perfil:**
- Organiza o racha, pelada ou equipe semiprofissional
- Cadastra jogadores, define escalação, registra resultados
- Principal alvo de conversão para o plano **Club**

**O que vê na plataforma:**
- Dashboard do time (elenco, partidas, campeonatos)
- Ferramentas de gestão: adicionar jogadores, agendar amistoso, criar campeonato
- Estatísticas do time e rankings

**Pode fazer:**
- Criar times (limitado por plano)
- Gerenciar elenco e comissão técnica
- Criar e confirmar amistosos
- Criar e administrar campeonatos

---

### 3.3 Dono de Time que Joga (sobreposição)

O caso mais comum no futebol amador. O usuário criou o time e também joga nele.

**Acúmulo técnico:**
- `players` → tem perfil de jogador
- `teams.owner_id` → é responsável pelo time
- `player_memberships` → está no elenco desse mesmo time

**Comportamento esperado:**
- Tem acesso à área de gestão do time
- Aparece no elenco como jogador
- Acumula estatísticas individuais normalmente
- Seus badges são concedidos pelas mesmas regras de qualquer jogador — não há distinção por ser dono do time

---

### 3.4 Membro da Comissão Técnica

Usuário vinculado a um time como parte da comissão técnica (técnico, preparador físico, médico etc.). Não é necessariamente jogador.

**Perfil:**
- Tem `staff_members` + `team_staff` criados pelo dono do time
- Pode ou não ter `players` preenchido (o técnico muitas vezes não joga)

**O que vê na plataforma:**
- Acesso ao time ao qual foi vinculado
- Elenco, escalação, estatísticas

> **Decisão em aberto:** o nível de permissão de um `staff_member` dentro do sistema ainda precisa ser definido. Ver `docs/product/authorization-rules.md`. Atualmente o schema suporta o vínculo, mas não diferencia poderes por papel da comissão.

---

### 3.5 Organizador de Campeonato

Usuário (com plano Club ou Liga) que criou e gerencia um campeonato. Pode ser ou não dono de um time participante.

**Perfil:**
- Cria o campeonato, define formato, fases, grupos e rodadas
- Confirma inscrições de times
- Registra resultados e encerra partidas
- Distribui prêmios ao final (`championship_awards`)

**O que vê na plataforma:**
- Painel do campeonato
- Tabela de classificação em tempo real
- Chaveamento de mata-mata
- Ferramenta de distribuição de prêmios e badges

---

### 3.6 Player Pro

Jogador que optou pelo plano de baixo custo para ampliar sua visibilidade pública.

**O que muda com o Player Pro:**
- Destaque visual nos rankings públicos (card diferenciado + seção "Destaques" antes da tabela orgânica)
- Cartão digital exportável em alta resolução
- URL amigável de perfil: `myclub.com.br/@nome`
- Badge permanente "Player Pro" no perfil público

**O que não muda:**
- Badges conquistados por performance continuam iguais e sempre visíveis — são prêmios, não features pagas
- Posição real no ranking orgânico — não é possível comprar posição
- Acesso ao perfil público básico — esse permanece free para todos

> **Progressão inclusiva:** quem assina Club ou Liga já tem todas as features do Player Pro embutidas — não é necessário assinar separado. O Player Pro existe como plano autônomo para jogadores que querem visibilidade sem nenhuma necessidade de gestão de time.

---

## 4. Navegação contextual

O menu e o dashboard do usuário são adaptados conforme os perfis que ele possui. O backend expõe os dados; o frontend decide o que exibir com base nos relacionamentos ativos do usuário autenticado.

| Perfil detectado | Seções exibidas |
| --- | --- |
| Só `users` (recém cadastrado) | Onboarding: completar perfil de jogador ou criar time |
| `players` | Meu Perfil, Meus Times, Meu Histórico, Meus Badges |
| `teams.owner_id` | Meus Times + área de gestão (Elenco, Partidas, Campeonatos) |
| `staff_members` | Times vinculados + acesso de comissão técnica |
| `players` + `teams.owner_id` | Tudo acima combinado |

> A presença do menu de gestão não é determinada por um campo de permissão — é determinada pela existência de `teams` onde o usuário é `owner_id`.

---

## 5. Fluxo de onboarding

```
Cadastro (users)
    ↓
Completar dados básicos
    ↓
┌──────────────────────────────────────────────────────────┐
│ Quero jogar num time   → preencher perfil de jogador     │
│ Quero criar meu time   → criar time (nome, modalidade)   │
│ Fazer depois           → entrar no dashboard             │
└──────────────────────────────────────────────────────────┘
    ↓ (jogador)                       ↓ (dono de time)
Preencher dados                  Criar time
(posição, birth_date...)         (nome, badge, modalidade)
    ↓                                 ↓
Aguardar adição               Adicionar jogadores
a um time pelo dono           ao elenco
    ↓                                 ↓
              Dashboard combinado
```

> Ambos os caminhos podem ser percorridos na mesma sessão. O usuário pode criar seu perfil de jogador e um time no mesmo onboarding.

---

## 6. Regras que dependem de outros documentos

| Regra | Documento responsável |
| --- | --- |
| O que cada plano desbloqueia | `docs/product/feature-gating.md` |
| Quem pode editar/excluir um time | `docs/product/authorization-rules.md` |
| Permissões da comissão técnica | `docs/product/authorization-rules.md` |
| Quem pode criar campeonatos | `docs/product/authorization-rules.md` |
| Jogador em múltiplos times | `docs/product/player-membership-rules.md` |
| Ciclo de vida de campeonatos | `docs/product/championship-lifecycle.md` |
| Fluxo de criação de amistosos | `docs/product/friendly-match-flow.md` |
| Perfil de descoberta de jogadores | ver seção 7 abaixo |

---

## 7. Perfil de descoberta de jogadores

### Contexto

Um dos diferencias do MyClub é permitir que times encontrem jogadores por posição e proximidade geográfica. No futebol amador, a captação de atletas ainda acontece por indicação, grupos de WhatsApp e redes sociais genéricas. A plataforma pode formalizar e digitalizar esse processo.

### Modelo de descoberta

O perfil de um jogador pode ser **descobrível** ou **privado**, controlado pelo próprio atleta.

Quando descobrível, o jogador aparece em buscas realizadas por donos de time com os seguintes filtros:

| Filtro | Base de dados |
| --- | --- |
| **Posição** | `position_id` do último `player_membership` ativo ou declarado no perfil |
| **Modalidade** | modalidade preferida declarada pelo jogador |
| **Região / Cidade** | campo de localização do perfil do jogador |
| **País** | campo de país do perfil |
| **Disponibilidade** | flag booleana — jogador aberto a convites |

### O que o time vê no resultado da busca

- Nome e foto do jogador
- Posição e modalidade
- Cidade / região
- Badges conquistados (sempre visíveis)
- Botão de "Convidar para o elenco"

O histórico detalhado de estatísticas só é visível se o atleta permitir (`history_public = true`).

### Campos a adicionar ao schema

| Campo | Tabela | Tipo | Observação |
| --- | --- | --- | --- |
| `is_discoverable` | `players` | boolean | default `false` — jogador aparece em buscas de times |
| `available_for_invite` | `players` | boolean | default `false` — aberto a convites ativos |
| `preferred_sport_mode_id` | `players` | bigint FK → `sport_modes` | modalidade preferida declarada |
| `city` | `players` | varchar(100) | cidade do jogador |
| `state` | `players` | varchar(60) | estado/província |
| `country` | `players` | char(2) | código ISO 3166-1 (ex: `BR`) |

### Decisões em aberto

- **Busca por proximidade real (GPS):** usar coordenadas geográficas ou apenas cidade/estado declarado? Geolocalization exige considerações de LGPD.
- **Visível para quem:** qualquer usuário logado pode buscar jogadores, ou apenas donos de time com plano Club+?
- **Ordenação dos resultados:** ordem alfabética, por badges, por última atividade? Player Pro tem destaque também nessa busca?
- **Notificação ao jogador:** quando um time visualiza o perfil dele ou envia convite, o jogador é notificado?
