# Restrições de Funcionalidades por Plano — MyClub

## Planos existentes

| Plano | Público-alvo | Preço estimado |
| --- | --- | --- |
| **Free** | Qualquer usuário | R$ 0 |
| **Player Pro** | Jogador que quer visibilidade pública | A definir |
| **Club** | Gestor de time | R$ 29–49/mês |
| **Liga** | Organizador de campeonatos regionais | R$ 89–149/mês |
| **Federação** | Federações e ligas organizadas | Sob consulta |

> **Progressão inclusiva:** Club inclui todas as features do Player Pro. Liga inclui tudo do Club. Quem paga Club ou Liga não precisa assinar o Player Pro separadamente — os benefícios de perfil já estão embutidos. O Player Pro existe para jogadores que querem os benefícios de visibilidade individual sem nenhuma necessidade de gestão de time.

---

## Tabela consolidada por funcionalidade

### Identidade e Perfil

| Feature | Free | Player Pro | Club | Liga |
| --- | --- | --- | --- | --- |
| Criar conta | ✅ | ✅ | ✅ | ✅ |
| Preencher perfil de jogador | ✅ | ✅ | ✅ | ✅ |
| Foto de perfil (avatar) | ✅ | ✅ | ✅ | ✅ |
| Perfil público compartilhável | ✅ | ✅ | ✅ | ✅ |
| Cartão digital básico | ✅ | ✅ | ✅ | ✅ |
| URL amigável `myclub.com.br/@nome` | ❌ | ✅ | ✅ | ✅ |
| Cartão digital exportável em alta resolução | ❌ | ✅ | ✅ | ✅ |
| Destaque visual em rankings públicos | ❌ | ✅ | ✅ | ✅ |
| Badge "Player Pro" permanente no perfil | ❌ | ✅ | ✅ | ✅ |
| Gráficos de evolução de desempenho por temporada | ❌ | ❌ | ❌ | ❌ ¹ |
| Comparativo percentual por posição e modalidade | ❌ | ❌ | ❌ | ❌ ¹ |

> ¹ Features de alta complexidade analítica — previstas apenas para o tier Federação (fase 3).

---

### Badges (por performance)

| Feature | Free | Player Pro | Club | Liga |
| --- | --- | --- | --- | --- |
| Receber badges por performance | ✅ | ✅ | ✅ | ✅ |
| Visualizar badges no próprio perfil | ✅ | ✅ | ✅ | ✅ |
| Badges visíveis no perfil público | ✅ | ✅ | ✅ | ✅ |

> **Regra imutável:** badges são prêmios de performance. Nunca são bloqueados por plano. Quem ganhou, tem — independente de qualquer assinatura.

---

### Times

| Feature | Free | Player Pro | Club | Liga |
| --- | --- | --- | --- | --- |
| Participar de times como jogador | ✅ | ✅ | ✅ | ✅ |
| Criar times | ✅ (1 time) | — | ✅ (ilimitado) | ✅ |
| Editar informações do time | ✅ | — | ✅ | ✅ |
| Upload de escudo do time | ✅ | — | ✅ | ✅ |
| Gerenciar elenco (adicionar/remover jogadores) | ✅ | — | ✅ | ✅ |
| Gerenciar comissão técnica | ✅ | — | ✅ | ✅ |
| Logo personalizado do time (marca própria) | ❌ | — | ✅ | ✅ |

---

### Amistosos

| Feature | Free | Player Pro | Club | Liga |
| --- | --- | --- | --- | --- |
| Criar amistosos | ✅ (ilimitado) | — | ✅ | ✅ |
| Confirmar/recusar amistoso | ✅ | — | ✅ | ✅ |
| Registrar resultado e estatísticas | ✅ | — | ✅ | ✅ |

---

### Campeonatos

| Feature | Free | Player Pro | Club | Liga |
| --- | --- | --- | --- | --- |
| Participar de campeonatos como jogador | ✅ | ✅ | ✅ | ✅ |
| Participar de campeonatos como time | ✅ | — | ✅ | ✅ |
| Criar campeonato formato `league` (pontos corridos) | ✅ (1 ativo) | — | ✅ (ilimitado) | ✅ |
| Criar campeonatos multi-fase (`knockout`, `cup`) | ❌ | — | ✅ | ✅ |
| Campeonatos com múltiplas rodadas | ❌ | — | ✅ | ✅ |
| Campeonatos públicos (visíveis externamente) | ❌ | — | ❌ | ✅ |

---

### Rankings e Estatísticas

| Feature | Free | Player Pro | Club | Liga |
| --- | --- | --- | --- | --- |
| Ver ranking do próprio time | ✅ | — | ✅ | ✅ |
| Ver ranking de campeonatos em que participa | ✅ | ✅ | ✅ | ✅ |
| Ver rankings públicos da plataforma | ✅ | ✅ | ✅ | ✅ |
| Destaque no ranking (card diferenciado) | ❌ | ✅ | ✅ | ✅ |
| Histórico completo de estatísticas por temporada | básico | completo | completo | completo |

---

### Plataforma

| Feature | Free | Player Pro | Club | Liga |
| --- | --- | --- | --- | --- |
| Sem anúncios AdSense | ❌ | ✅ | ✅ | ✅ |
| API de leitura | ❌ | — | ❌ | ✅ |
| White-label / integração externa | ❌ | — | ❌ | Federação |

---

## Regras de convivência entre planos

- A progressão é **inclusiva**: Free → Player Pro → Club → Liga → Federação; cada nível engloba tudo do anterior
- **Club** inclui todas as features do **Player Pro** para o perfil individual do usuário pagante — sem necessidade de assinar separado
- **Liga** inclui tudo do **Club** mais features de organização regional
- O plano não se propaga para outros usuários: um jogador do elenco de um time Club **não** herda benefits do Club — cada usuário tem seu próprio plano
- Anúncios AdSense são exibidos para **todos** no tier Free, incluindo jogadores puros; apenas o usuário que paga (Player Pro, Club, Liga) tem anúncios removidos no próprio acesso

---

## Decisões em aberto

- Preço final do Player Pro (estimativa: R$ 3,90–9,90/mês)
- Limite de campeonatos ativos simultâneos em planos Club e Liga (atualmente sem definição)
- Critério exato de "histórico básico" vs "histórico completo" no plano Free (quais campos/períodos são visíveis)
