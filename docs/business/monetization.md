# Estudo de Monetização — MyClub

## Contexto do produto

O MyClub é uma plataforma de gestão esportiva voltada para **futebol amador e semiprofissional**: times, campeonatos, amistosos, elencos, estatísticas individuais e rankings. O público-alvo imediato é o gestor de time/campeonato, e o público extensível inclui jogadores, árbitros e organizadores de ligas.

O mercado brasileiro de futebol amador é um dos maiores do mundo em volume de participantes, mas é extremamente **sensível a preço** e fragmentado. Qualquer estratégia de monetização precisa equilibrar acessibilidade com sustentabilidade financeira.

Este documento avalia os modelos com maior potencial, analisa os competidores que já os empregam e sugere uma combinação realista para o estágio atual do projeto.

---

## Índice

1. [Análise de mercado e competidores](#1-análise-de-mercado-e-competidores)
2. [Modelos avaliados](#2-modelos-avaliados)
3. [Comparativo por viabilidade](#3-comparativo-por-viabilidade)
4. [Estratégia recomendada](#4-estratégia-recomendada)
5. [Roadmap de monetização por fase](#5-roadmap-de-monetização-por-fase)
6. [Riscos e contrapontos](#6-riscos-e-contrapontos)

---

## 1. Análise de mercado e competidores

### Plataformas de referência pesquisadas

| Plataforma       | Mercado alvo     | Modelo principal                      | Observação                                                                 |
| ---------------- | ---------------- | ------------------------------------- | -------------------------------------------------------------------------- |
| **TeamSnap**     | EUA / jovens     | Freemium → assinatura mensal por time | US$ 12–20/mês por time; planos para clubes com preço customizado           |
| **LeagueApps**   | EUA / ligas      | Taxa sobre transação (% do pagamento) | Sem mensalidade; cobra ~3–5% de cada inscrição processada pela plataforma  |
| **Pitchero**     | Reino Unido      | Freemium → assinatura por clube       | £0 (time único) → £38/mês (Standard) → £99/mês (Pro); fee sobre pagamentos |
| **SportsEngine** | EUA / federações | SaaS B2B + add-ons                    | Voltado a organizações grandes; preço via demo                             |
| **Spond**        | Europa           | Gratuito + patrocínio local embutido  | Completamente free para usuário; receita via marketplace de patrocinadores |

### Lições extraídas

- **Freemium é dominante** no setor. Bloquear features básicas desde o início afasta a base de usuários antes de qualquer monetização.
- **Taxa sobre transação** (LeagueApps) funciona muito bem quando a plataforma processa pagamentos. Sem fluxo financeiro dentro da plataforma, esse modelo não se aplica.
- **Patrocínio local embutido** (Spond) é especialmente promissor para o contexto brasileiro, onde pequenos comércios locais (bares, academias, lojas de material esportivo) têm forte vínculo com times de futebol amador.
- **White-label para federações/ligas** é o modelo de maior ticket, porém exige o produto maduro.

---

## 2. Modelos avaliados

### 2.1 Assinatura SaaS (Freemium → Pago)

**Como funciona:** o gestor do time ou da liga usa gratuitamente com limitações (ex: máximo de jogadores, sem rankings, sem badges). Para desbloquear features avançadas, paga uma mensalidade ou anuidade.

**Referências:** TeamSnap, Pitchero, Playbookhq.

**Adequação ao MyClub:** ★★★★☆

O modelo se encaixa bem porque o MyClub já possui uma separação natural entre features básicas (cadastro, elenco, partidas) e features avançadas (rankings, badges, estatísticas detalhadas, championships multi-fase). A friction de adoção é baixa: o gestor começa grátis e migra quando sentir valor.

**Estrutura sugerida de planos:**

| Plano           | Preço estimado (BR) | Limites / Features                                                                                          |
| --------------- | ------------------- | ----------------------------------------------------------------------------------------------------------- |
| **Free**        | R$ 0                | 1 time, até 20 jogadores, campeonatos simples (league), amistosos ilimitados                                |
| **Player Pro**  | A definir           | Destaque em rankings públicos, cartão exportável, URL amigável, gráficos de evolução, badge "Player Pro"    |
| **Club**        | R$ 29–49 / mês      | Times ilimitados, todos os formatos de campeonato, rankings, histórico completo, sem anúncios               |
| **Liga**        | R$ 89–149 / mês     | Multi-time, gestão de campeonatos públicos, painel de árbitros, API de leitura, logo personalizado          |
| **Federação**   | Sob consulta        | White-label, SLA, integração com sistemas externos, suporte dedicado                                        |

> Preços calibrados para a realidade do mercado de futebol amador brasileiro; revisáveis com tração.

**Prós:**

- Receita recorrente e previsível (MRR)
- Modelo amplamente validado no setor
- Fácil de comunicar ("assine como o Spotify do seu time")

**Contras:**

- Exige base de usuários para gerar receita relevante
- Churn alto em produtos sem engajamento consistente

---

### 2.2 Taxa sobre transação (Payment Processing Fee)

**Como funciona:** a plataforma processa inscrições pagas em campeonatos (taxa de inscrição do time, mensalidade de jogadores) e retém uma porcentagem de cada transação.

**Referências:** LeagueApps (~3–5%), Pitchero (1,67–2,58% + taxa fixa).

**Adequação ao MyClub:** ★★★☆☆

Viável apenas se o MyClub implementar um módulo de pagamentos (via Stripe, Pagar.me, Asaas ou similar). Sem isso, o modelo não existe. É complementar — não substituto — ao SaaS.

**Prós:**

- Receita proporcional ao volume da plataforma
- "Você paga quando você arrecada" facilita adoção
- Funciona muito bem para ligas e campeonatos com inscrição paga

**Contras:**

- Requer integração com gateway de pagamento (pressão regulatória no BR: KYC, split de pagamento)
- Competição com PIX/transferência direta, que é o padrão atual nos times amadores
- Agrega complexidade técnica e operacional considerável

> **Recomendação:** deixar para o roadmap do plano Liga/Federação, onde o volume justifica o investimento. Não bloquear a v1.

---

### 2.3 Patrocínio local integrado à plataforma

**Como funciona:** pequenas e médias empresas locais (bares, academias, lojas esportivas, uniformes, suplementos) pagam para aparecer **dentro da experiência** da plataforma — banner no perfil do time, menção no relatório de campeonato, badge patrocinado, logo no cartão do jogador.

**Referências:** Spond (modelo completamente free sustentado por patrocinadores), TeamSnap Sponsorships.

**Adequação ao MyClub:** ★★★★★

Este é potencialmente o modelo com **maior fit cultural** para o Brasil. O vínculo entre times amadores e comércios locais é orgânico e pré-existe: a "camiseta patrocinada pelo bar do Zé" é um fenômeno cultural. A plataforma pode formalizar e digitalizar essa relação, criando um marketplace de patrocínio hiperlocal.

**Formatos possíveis:**

| Formato                      | Quem paga                  | O que aparece na plataforma                                    |
| ---------------------------- | -------------------------- | -------------------------------------------------------------- |
| **Patrocínio de time**       | Empresa local              | Logo no perfil do time, menção em partidas, banner no app      |
| **Patrocínio de campeonato** | Empresa local ou regional  | "Campeonato Copinha patrocinado por X", logo em todas as telas |
| **Badge patrocinado**        | Marca esportiva (ex: Nike) | "Artilheiro da Temporada — by Nike"                            |
| **Boost de visibilidade**    | Qualquer empresa           | Destaque no ranking público de times / campeonatos da região   |
| **Material kit**             | Loja de uniformes          | Oferta de desconto exibida no perfil do time                   |

**Prós:**

- Receita sem cobrar do usuário final (time / jogador)
- Altamente escalável horizontalmente (cada cidade tem seus patrocinadores)
- Cria um ecossistema com stakeholders que não são usuários diretos da plataforma

**Contras:**

- Precisa de base de usuários para atrair patrocinadores ("não há anúncio sem audiência")
- Requer um módulo de self-service para empresas (ou uma equipe comercial)
- Risco: se mal executado, degrada a experiência do usuário

---

### 2.4 Player Pro (Plano do Jogador)

**Como funciona:** o próprio jogador paga um valor de baixo custo para ampliar sua visibilidade pública na plataforma. Não compra posição no ranking orgânico — compra destaque de identidade e ferramentas de apresentação pessoal.

**Referências:** não há concorrente direto no nicho futebol amador BR. O modelo mais próximo é o LinkedIn Premium adaptado para atletas amadores.

**Adequação ao MyClub:** ★★★☆☆

Menor potencial de receita absoluta no curto prazo, mas alta relevância estratégica: é o único plano que monetiza diretamente o jogador, o usuário mais numeroso na plataforma. Conforme o perfil público se torna um "currículo esportivo" reconhecido, a proposta de valor cresce organicamente.

**Features do Player Pro:**

| Feature | Descrição |
| --- | --- |
| Destaque em rankings públicos | Card diferenciado + seção "Destaques" antes da tabela orgânica, claramente rotulada |
| Cartão digital exportável em alta resolução | Compartilhável em WhatsApp e Instagram Stories |
| URL amigável de perfil | `myclub.com.br/@nome` — identidade fácil de compartilhar |
| Gráficos de evolução de desempenho | Gols, assistências e participações por temporada |
| Comparativo percentual por posição | "Você está entre os top 10% dos MEIAs da sua cidade" |
| Badge "Player Pro" permanente | Sinaliza comprometimento com o esporte no perfil público |

> **Importante:** badges conquistados por performance (artilheiro, bola de ouro etc.) são **sempre gratuitos** — são prêmios, não features. O Player Pro não afeta a concessão ou visibilidade de badges de performance. O badge "Player Pro" é um distintivo de assinatura, separado dos badges de conquistas.

**Relação com outros planos:**

- Player Pro e Club são **ortogonais**: um jogador pode assinar Player Pro individualmente, mesmo que o time onde joga esteja no plano Free
- Um dono de time no plano Free que assina Player Pro continua com o limite de 1 time — o Player Pro afeta apenas o perfil individual do atleta
- O plano Club cobre o contexto gerencial (time, elenco, campeonatos); o Player Pro cobre o perfil pessoal do atleta

**Prós:**

- Monetiza diretamente o usuário mais engajado (o jogador que quer se destacar)
- Ticket acessível para o público-alvo (futebol amador é sensível a preço)
- Baixo custo de implementação (extensão das features de perfil já planejadas)
- Pode crescer organicamente se o perfil virar referência para captação de jogadores
- Não cria injustiça percebida: rankings orgânicos permanecem intactos

**Contras:**

- Ticket baixo — receita unitária pequena, requer volume
- Difícil de justificar valor sem uma base de rankings públicos já consolidada

---

### 2.5 White-label / Licenciamento de API para Federações

**Como funciona:** federações estaduais e municipais de futebol amador, associações e ligas organizadas licenciam a infraestrutura do MyClub como plataforma própria (branded) ou integram via API.

**Referências:** SportsEngine, LeagueApps (versão enterprise).

**Adequação ao MyClub:** ★★★★☆ (médio/longo prazo)

O ticket médio é muito mais alto (R$ 500–5.000/mês por federação), mas o ciclo de venda é longo e exige produto maduro. É o caminho natural após validação com usuários individuais.

**Prós:**

- Maior ticket por cliente
- Contrato recorrente com baixo churn (federação não troca de plataforma facilmente)
- Legitima o produto no mercado

**Contras:**

- Ciclo de vendas longo (3–12 meses)
- Exige personalização e suporte mais complexo
- Só faz sentido com o produto estável e com casos de uso validados

---

### 2.6 AdSense / Publicidade programática (MVP)

**Como funciona:** exibir anúncios do Google Ads em posições estratégicas da plataforma, com regras rígidas de placement para não degradar a experiência.

**Adequação ao MyClub:** ★★★☆☆ **(somente na Fase 1 — MVP)**

AdSense em modo "spray and pray" (anúncio em toda página) é uma péssima ideia para qualquer produto. Mas aplicado com critério cirúrgico durante o MVP, pode cobrir custos operacionais básicos enquanto a base de usuários não é grande o suficiente para os outros modelos.

**Princípio orientador:** o anúncio nunca deve aparecer onde o usuário está trabalhando. Ele aparece onde o usuário já terminou.

#### Modelo de placement inteligente

| Posição                          | Quando aparece                                  | Formato                     | Justificativa                                                          |
| -------------------------------- | ----------------------------------------------- | --------------------------- | ---------------------------------------------------------------------- |
| **Pós-ação dashboard**           | Após salvar partida, registrar campeonato, etc. | Banner 728×90 (leaderboard) | Usuário acabou a tarefa — janela de atenção natural                    |
| **Sidebar em listas longas**     | Listagem de times, jogadores, campeonatos       | Banner 300×250 (rectangle)  | Área lateral sem interferir no conteúdo principal                      |
| **Tela de resultado de partida** | Após encerrar uma partida                       | Banner 320×50 (mobile)      | Momento de "celebração" — tolerância alta a interrupção                |
| **Página pública de perfil**     | Perfil de time / jogador visitado por terceiros | Banner 300×250              | Visitante anônimo, sem expectativa de uso — maior tolerância a anúncio |

#### O que NUNCA recebe anúncio

- Formulários de criação/edição (time, campeonato, partida, elenco)
- Tabelas de dados (rankings, estatísticas, escalação)
- Fluxos de autenticação (login, cadastro)
- Modal ou overlay de qualquer tipo
- Qualquer tela mobile que não seja a de resultado

#### Modelo de incentivo à remoção

O anúncio funciona como **upgrade driver passivo**: toda vez que um anúncio é exibido, aparece abaixo dele uma linha discreta: _"Remova os anúncios com o plano Club — a partir de R$ 29/mês."_

Isso transforma o incômodo controlado em argumento de conversão, sem ser intrusivo.

#### Estimativa de receita na Fase 1

| Métrica                        | Estimativa conservadora |
| ------------------------------ | ----------------------- |
| Sessões/mês (100 times ativos) | ~8.000–15.000           |
| RPM médio BR (nicho esporte)   | R$ 8–15 / mil pageviews |
| Receita mensal estimada        | R$ 64–225 / mês         |

Não é transformador, mas cobre custos de hospedagem e domínio no início sem nenhuma fricção de cobrança.

**Prós:**

- Zero friction de cobrança — não exige cadastro de cartão, PIX ou nada
- Implementação em horas (tag no layout)
- Cobre custos operacionais básicos durante a validação
- Funciona como incentivo orgânico ao upgrade

**Contras:**

- Receita baixa em volumes pequenos
- Requer política de placement disciplinada (violá-la destrói a experiência)
- Anúncios programáticos podem exibir conteúdo fora do contexto esportivo — mitigar com categorias bloqueadas no Google Ads (gambling, política, conteúdo adulto)
- **Deve ser desativado ou opcional a partir da Fase 2**, quando o patrocínio local é mais qualificado e rentável

> A estratégia de saída do AdSense é clara: quando o módulo de patrocínio local estiver ativo (Fase 2), o AdSense deixa de ser exibido para os planos pagos e vai sendo substituído por patrocinadores contextuais nos planos gratuitos.

---

### 2.7 Doações (Donate / Open Source)

**Como funciona:** os usuários contribuem voluntariamente (modelo Patreon, Ko-fi, Open Collective).

**Adequação ao MyClub:** ★★☆☆☆

Funciona bem para projetos genuinamente open-source com comunidade de desenvolvedores (ex: Mastodon, VLC). Para uma plataforma de produto com usuário final não-técnico, o montante gerado raramente sustenta a operação.

**Quando faria sentido:** se o MyClub optasse por um modelo 100% open-source (código aberto, autohosting liberado) e buscasse sustentação via doações + serviços gerenciados (modelo "open-core"). Isso é viável mas exige uma estratégia muito distinta das demais.

---

## 3. Comparativo por viabilidade

| Modelo                        | Receita potencial | Complexidade de impl. | Fit com BR amador | Fase ideal                  |
| ----------------------------- | ----------------- | --------------------- | ----------------- | --------------------------- |
| Assinatura SaaS (Freemium)    | ★★★★☆             | ★★☆☆☆ (baixa)         | ★★★★☆             | v1 (MVP) → contínuo         |
| Patrocínio local integrado    | ★★★★★             | ★★★☆☆ (média)         | ★★★★★             | v2                          |
| White-label / API             | ★★★★★             | ★★★★☆ (alta)          | ★★★★☆             | v3                          |
| Taxa sobre transação          | ★★★★☆             | ★★★★☆ (alta)          | ★★★☆☆             | v3                          |
| Player Pro (plano do jogador)  | ★★☆☆☆             | ★☆☆☆☆ (baixa)         | ★★★☆☆             | v2                          |
| AdSense (placement cirúrgico) | ★★☆☆☆             | ★☆☆☆☆ (mínima)        | ★★☆☆☆             | v1 apenas — transição em v2 |
| Donates                       | ★☆☆☆☆             | ★☆☆☆☆ (mínima)        | ★☆☆☆☆             | Não recomendar              |

---

## 4. Estratégia recomendada

A combinação de maior potencial para o MyClub no horizonte de 3 anos é:

```
Freemium SaaS (base)  +  Patrocínio local (amplificador)  +  White-label (topo do funil B2B)
```

### Por que essa combinação?

**Freemium SaaS** resolve o problema de aquisição: gestores de time precisam testar antes de pagar. A gratuidade da versão básica é o principal driver de crescimento orgânico. A conversão para pago acontece naturalmente quando a plataforma se tornar parte da rotina do time.

**Patrocínio local** é o diferencial competitivo. Nenhuma plataforma global faz bem o hiperlocal brasileiro. Um bar que patrocina 3 times locais no MyClub por R$ 99/mês gera mais receita do que 10 gestores de time pagando plano Club. E libera o usuário de pagar, acelerando a adoção.

**White-label para federações** é a aposta de longo prazo. Federações estaduais de futebol amador têm dezenas de ligas sob sua gestão — uma única federação pode valer mais do que centenas de times individuais.

---

## 5. Roadmap de monetização por fase

### Fase 1 — MVP / Validação (meses 1–6)

**Foco: construir a base + cobertura de custos via AdSense cirúrgico.**

- Lançar plano Free sem restrições relevantes
- Ativar AdSense com placement restrito (ver § 2.6): apenas pós-ação, sidebar e perfil público
- Bloquear categorias sensíveis no Google Ads (gambling, conteúdo adulto, política)
- Exibir sob cada anúncio: _"Remova os anúncios com o plano Club"_ — preparando o terreno para conversão na Fase 2
- Validar que gestores usam a plataforma consistentemente
- Coletar dados de uso para definir onde o paywall faz sentido
- Identificar os primeiros "super-usuários" (gestores de ligas pequenas, campeonatos locais)
- Monitorar CTR e RPM real — se RPM cair abaixo de R$ 5, avaliar desativar antes da Fase 2

**Meta:** 50–100 times ativos, 500+ jogadores cadastrados, custos de infra cobertos pelo AdSense.

---

### Fase 2 — Monetização Inicial (meses 6–18)

**Ativar: Plano Club pago + primeiros patrocinadores.**

- Lançar o plano Club (R$ 29–49/mês) com features que os super-usuários já usam e valorizam:
    - Rankings detalhados
    - Histórico completo de estatísticas
    - Campeonatos multi-fase (knockout, cup)
    - Sem anúncios
- Lançar o **Player Pro** com preço a definir (~R$ 3,90–9,90/mês): destaque em rankings, cartão exportável, URL amigável, gráficos de evolução
- Criar um módulo de patrocínio simples: upload de logo, escolha do time/campeonato, pagamento via PIX/cartão

> **Nota sobre badges:** badges de performance são concedidos automaticamente ao encerrar campeonatos e são sempre visíveis para qualquer jogador, em qualquer plano. Nunca são feature paga.

**Meta:** 5% dos times convertidos para pago, 10–20 patrocinadores locais ativos.

---

### Fase 3 — Escala (meses 18–36)

**Ativar: Plano Liga + White-label + taxa sobre transação.**

- Lançar plano Liga para organizadores de campeonatos regionais
- Iniciar conversas com federações estaduais de futebol amador
- Implementar processamento de inscrições pagas (Pagar.me / Stripe)
- Cobrar taxa de transação de 2–3% sobre inscrições processadas
- API pública documentada para integrações externas

**Meta:** 1–3 clientes white-label ou Liga, MRR de R$ 10k+.

---

## 6. Riscos e contrapontos

### "AdSense não afasta usuários na Fase 1?"

Depende de onde o anúncio aparece. Pesquisas de UX (Nielsen Norman Group, 2023) mostram que usuários toleram anúncios em **momentos de pausa** — após completar uma ação, em páginas de browsing, em perfis públicos — e rejeitam anúncios em **momentos de foco** — formulários, tabelas, fluxos de entrada de dados.

O modelo proposto na Fase 1 respeita essa distinção: anúncio só aparece onde o usuário já terminou o que veio fazer. A experiência de trabalho permanece limpa.

Além disso, o AdSense aqui **não é um modelo de negócio** — é um **custeio temporário de infra**. A saída está planejada: planos pagos sem anúncio (Fase 2) e substituição por patrocínio contextual (Fase 2+).

### "Por que não ser completamente gratuito como o Instagram?"

O Instagram não é gratuito — é financiado por US$ bilhões em publicidade porque tem **bilhões de usuários**. O MyClub não tem e não terá essa escala no curto prazo. Publicidade programática genérica em volumes pequenos gera renda insignificante e prejudica a experiência quando mal posicionada.

A analogia mais precisa com o MyClub não é o Instagram, mas o **Notion** ou o **Canva**: produto que começa gratuito, cresce pela qualidade, e monetiza as camadas de maior uso. O AdSense na Fase 1 é apenas o "colchão" enquanto a base ainda não justifica os modelos maiores.

### "O futebol amador brasileiro não paga por software"

Verdade parcialmente. O gestor de time amador **já paga** por:

- Uniforme (R$ 80–200/jogador)
- Campo (R$ 150–500 por jogo)
- Árbitro (R$ 100–300 por jogo)
- Troféus e premiações (R$ 500–2.000 por campeonato)

Pagar R$ 29–49/mês por uma plataforma que organiza tudo isso é uma fração do custo operacional. O problema não é disposição de pagar — é **percepção de valor**. Daí a importância de entregar valor real na versão gratuita primeiro.

### "Patrocinadores locais são difíceis de captar"

No início, sim. Por isso o modelo de patrocínio só entra na Fase 2, quando já há audiência para mostrar ao patrocinador. Com 500+ usuários ativos numa cidade, um bar local consegue enxergar valor em R$ 99/mês para aparecer nos campeonatos daquela região.

### "White-label canibaliza o produto principal"

Não obrigatoriamente. A versão white-label pode ser um produto paralelo com escopo fechado (ex: "MyClub para Federações"), sem impactar o desenvolvimento da plataforma pública. O risco real é desvio de foco em fase muito inicial — por isso entra apenas na Fase 3.
