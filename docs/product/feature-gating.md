# Restrições de Funcionalidades por Plano — MyClub

## Planos existentes

| Plano | Público-alvo | Preço estimado |
| --- | --- | --- |
| **Free** | Qualquer usuário | R$ 0 |
| **Player Pro** | Jogador que quer visibilidade pública | A definir |
| **Club** | Gestor de time | R$ 29–49/mês |
| **Liga** | Organizador de campeonatos regionais | R$ 89–149/mês |
| **Federação** | Federações e ligas organizadas | Sob consulta |

> **Player Pro e Club são ortogonais.** Um jogador pode assinar o Player Pro individualmente, mesmo que o time onde joga esteja no Free. O plano Club afeta o contexto de gestão do time; o Player Pro afeta o perfil pessoal do atleta.

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
| URL amigável `myclub.com.br/@nome` | ❌ | ✅ | — | — |
| Cartão digital exportável em alta resolução | ❌ | ✅ | — | — |
| Destaque visual em rankings públicos | ❌ | ✅ | — | — |
| Gráficos de evolução de desempenho por temporada | ❌ | ✅ | — | — |
| Comparativo percentual por posição e modalidade | ❌ | ✅ | — | — |
| Badge "Player Pro" permanente no perfil | ❌ | ✅ | — | — |

> **Nota:** o símbolo `—` indica que a feature não é aplicável àquele plano pelo contexto (ex: Player Pro é um plano individual, não de gestão).

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
| Logo personalizado do time (marca própria) | ❌ | — | ❌ | ✅ |

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
| Gerenciar painel de árbitros | ❌ | — | ❌ | ✅ |

---

### Rankings e Estatísticas

| Feature | Free | Player Pro | Club | Liga |
| --- | --- | --- | --- | --- |
| Ver ranking do próprio time | ✅ | — | ✅ | ✅ |
| Ver ranking de campeonatos em que participa | ✅ | ✅ | ✅ | ✅ |
| Ver rankings públicos da plataforma | ✅ | ✅ | ✅ | ✅ |
| Destaque no ranking (card diferenciado) | ❌ | ✅ | — | — |
| Histórico completo de estatísticas por temporada | básico | completo | completo | completo |
| Exportar relatório de estatísticas do time | ❌ | — | ✅ | ✅ |

---

### Plataforma

| Feature | Free | Player Pro | Club | Liga |
| --- | --- | --- | --- | --- |
| Sem anúncios AdSense | ❌ | ✅ | ✅ | ✅ |
| API de leitura | ❌ | — | ❌ | ✅ |
| White-label / integração externa | ❌ | — | ❌ | Federação |

---

## Regras de convivência entre planos

- **Player Pro** e **Club** são planos ortogonais: podem coexistir no mesmo usuário, cobrem contextos distintos
- Um dono de time no **Free** que assina **Player Pro** continua com limite de 1 time — o Player Pro não afeta limites de gestão
- O plano **Club** não inclui features do Player Pro (URL amigável, destaque de jogador etc.) porque são contextos distintos — um gestor de time que também quer visibilidade como jogador assina ambos
- **Liga** engloba tudo do **Club** mais features de organização regional

---

## Decisões em aberto

- Preço final do Player Pro (estimativa: R$ 3,90–9,90/mês)
- Limite exato de jogadores por elenco no plano Free (referência: 20 jogadores do legado)
- Se o plano Free permite 1 campeonato ativo simultâneo ou se o limite é outro critério
- Se anúncios AdSense aparecem apenas para usuários que têm gestão de time, ou também para jogadores puros no Free
- Se o plano Club remove anúncios apenas para o gestor, ou para todos os jogadores do time
