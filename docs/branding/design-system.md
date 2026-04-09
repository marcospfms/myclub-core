# Design System — MyClub

## 1. Papel do design system

O design system do MyClub deve servir duas funções ao mesmo tempo:

- garantir consistência visual e comportamental
- acelerar implementação de telas administrativas e futuras superfícies públicas

Ele não é apenas uma biblioteca de componentes.
Ele é a tradução operacional da marca em interface.

Ele também precisa servir superfícies com naturezas diferentes:

- admin operacional em Inertia
- páginas públicas e rankings consumidos por outras stacks
- cartões e perfis compartilháveis
- superfícies futuras com patrocínio local

---

## 2. Princípios do sistema

### 2.1 Clareza operacional

Toda tela deve deixar evidente:

- onde o usuário está
- o que ele pode fazer
- qual é a próxima ação
- qual foi o resultado da ação

### 2.2 Energia controlada

A interface deve ter identidade esportiva, mas sem perder legibilidade e densidade operacional.

### 2.3 Hierarquia forte

Elementos importantes devem parecer importantes.
Não nivelar visualmente:

- título
- texto auxiliar
- métrica
- ação primária
- ação destrutiva

### 2.4 Consistência sem monotonia

O sistema deve ter padrões repetíveis, mas não pode parecer burocrático ou morto.

---

## 3. Sistema cromático na interface

## 3.1 Semântica principal

- verde = ação principal, progresso, confiança
- dourado = destaque, conquista, premium
- vermelho = perigo, exclusão, falha
- amarelo = atenção, aviso, dependência de ação
- azul = informação e contexto técnico
- neutros = estrutura e leitura

### Regra crítica de produto

Cor não pode misturar semânticas que o produto separa claramente:

- mérito esportivo
- destaque visual de assinatura
- estado operacional
- perigo/erro

## 3.2 Regras de aplicação

- não usar mais de uma cor de destaque forte por bloco sem necessidade
- ações primárias da mesma tela devem compartilhar a mesma hierarquia
- cores semânticas não devem ser reutilizadas como decoração arbitrária

---

## 4. Elevação e superfícies

### Níveis recomendados

#### Nível 0

- fundo base da aplicação

#### Nível 1

- cards principais
- seções de formulário

#### Nível 2

- modais
- dropdowns
- popovers

### Regra

Preferir contraste por borda + cor + leve sombra.
Evitar sombras pesadas como principal mecanismo de separação.

---

## 5. Layout e grid

### Página administrativa

Estrutura recomendada:

- header contextual
- métricas resumidas quando fizer sentido
- conteúdo principal em cards ou painéis
- ações primárias próximas do contexto principal

### Larguras

- conteúdo padrão: `max-w-7xl`
- formulários simples: `max-w-3xl` ou `max-w-4xl`
- leitura densa: preservar colunas que não fiquem excessivamente longas

---

## 6. Navegação

## 6.1 Sidebar

Deve comunicar:

- estabilidade
- domínio
- acesso rápido

### Regras

- grupos por domínio
- ícones consistentes
- label curto
- item ativo com destaque claro

## 6.2 Breadcrumb

Usar breadcrumb para:

- localização
- contexto hierárquico
- retorno previsível

Não usar breadcrumb como substituto de título.

---

## 7. Headers de página

Todo módulo admin deve ter um cabeçalho claro com:

- título
- descrição curta
- ação principal quando aplicável

Quando existir risco operacional, incluir também:

- indicadores de volume
- estados importantes
- avisos relevantes

Quando a página for pública ou compartilhável, o header pode ganhar:

- mais assinatura visual
- presença de logo/símbolo
- espaço para selo de campeonato, badge ou patrocínio

---

## 8. Botões

## 8.1 Hierarquia

### Primário

- verde da marca
- ação principal da tela

### Secundário

- fundo claro ou borda
- ações úteis, mas não centrais

### Ghost

- ações leves e contextuais

### Danger

- exclusões
- ações irreversíveis

## 8.2 Regras

- botão sempre com `cursor: pointer`
- rótulo direto
- evitar mais de um botão primário competindo no mesmo bloco
- estados `disabled`, `loading`, `hover`, `focus` devem ser explícitos

---

## 9. Inputs e formulários

## 9.1 Estrutura esperada

Cada campo deve ter:

- label claro
- ajuda opcional
- erro abaixo do campo
- espaçamento suficiente

## 9.2 Cards de formulário

Blocos de formulário devem ter:

- `CardHeader` com respiro superior real
- títulos descritivos
- texto auxiliar curto e orientado à regra

## 9.3 Regras

- não esconder regra importante apenas em placeholder
- placeholder complementa, não substitui label
- campos relacionados devem ficar próximos

---

## 10. Tabelas

As tabelas são centrais no admin e devem parecer ferramenta de operação, não planilha crua.

### Regras

- cabeçalhos claros
- espaçamento respirável
- ações por linha com leitura rápida
- ícones quando agregarem reconhecimento
- vazios bem tratados com empty state

### Colunas de ícone

Quando uma entidade possui `icon`, exibir:

- ícone renderizado
- chave textual ou nome técnico de apoio, quando útil

### Regras específicas para rankings

- o ranking orgânico precisa ter contraste e leitura imediata
- destaques do Player Pro devem ser visuais, mas não podem falsear ordem
- métricas de desempenho precisam ser claramente separadas de elementos promocionais

---

## 11. Cards e métricas

Cards de métrica devem mostrar:

- nome da métrica
- valor principal
- contexto ou tendência quando houver

Evitar:

- excesso de ornamento
- cores fortes em todos os cards ao mesmo tempo

---

## 12. Badges, chips e status

Usar badges para:

- status
- escopo
- categoria
- destaque

### Regras

- badge precisa ser semanticamente útil
- não usar badge só para enfeitar labels simples
- respeitar semântica cromática

### Diferenças obrigatórias

#### Badge de performance

- representa conquista real
- deve ter maior peso simbólico

#### Badge de plano

- representa assinatura
- precisa ser reconhecível, mas visualmente distinto de mérito esportivo

#### Badge de status operacional

- representa estado de sistema
- deve ser o mais funcional dos três

---

## 13. Empty states

Um empty state bom deve informar:

- o que está vazio
- por que isso importa
- qual ação tomar

Não usar empty state puramente decorativo.

Quando fizer sentido, um empty state pode orientar o upgrade de plano, mas:

- sem bloquear compreensão básica
- sem parecer chantagem visual
- sem ocupar o lugar da ação principal do usuário

---

## 14. Toasts e feedback

O sistema de feedback deve cobrir:

- sucesso
- aviso
- neutro
- erro

### Regras

- sucesso confirma conclusão
- warning sinaliza atenção sem bloquear
- neutro informa mudança de estado ou contexto
- error deve ser claro e acionável

Em fluxos de amistoso, campeonato e elenco, feedbacks devem ser especialmente explícitos por envolverem estados multi-etapa e impacto em histórico.

---

## 15. Motion

Motion deve ser funcional.

### Aplicações recomendadas

- entrada suave de toast
- expansão de menus
- transição curta de estado de loading
- mudança de aba ou painel

### Evitar

- animação excessiva
- bounce sem propósito
- elementos “flutuando” gratuitamente

### Direção

- duração curta
- easing natural
- movimento discreto

---

## 16. Estados de carregamento

Usar skeleton ou estados de espera quando:

- a área for previsível
- o conteúdo tiver estrutura conhecida

Usar spinner apenas para ações curtas ou inline.

---

## 18. Stack de implementação — shadcn-vue + Tailwind v4

### Biblioteca de componentes

O design system do MyClub usa **shadcn-vue** como base de componentes, com estilo `new-york-v4`. Isso significa:

- **Não criar componentes de UI do zero** — usar os primitivos do shadcn-vue e customizar via token
- Instalar componentes via CLI: `npx shadcn-vue@latest add button card table ...`
- Componentes ficam em `resources/js/components/ui/` e são editáveis diretamente

### Ícones

Biblioteca de ícones: **Lucide** (configurado em `components.json`). Usar o componente `<LucideIcon>` ou importar ícones individualmente de `lucide-vue-next`.

### Tailwind v4 — como os tokens funcionam

O projeto usa **Tailwind CSS v4** com CSS variables. A sintaxe muda em relação ao v3:

- Tokens definidos no bloco `:root` do `app.css` (e `.dark` para dark mode)
- O bloco `@theme inline` em `app.css` mapeia variáveis CSS em classes utilitárias Tailwind
- Não há `tailwind.config.js` com `extend.colors` — a extensão é feita diretamente no CSS

```css
/* Como funciona no app.css */
@theme inline {
    --color-primary: var(
        --primary
    ); /* → classe Tailwind: bg-primary, text-primary */
    --color-sidebar: var(
        --sidebar-background
    ); /* → classe Tailwind: bg-sidebar */
}

:root {
    --primary: hsl(151 71% 24%); /* Club Green */
}
```

### Como sobrescrever tokens do shadcn com a paleta MyClub

Todos os tokens shadcn estão mapeados para a paleta MyClub em `resources/css/app.css`. Para ajustar pontualmente:

```css
/* Em app.css, dentro de :root ou de um seletor específico */
:root {
    --primary: hsl(151 71% 24%); /* Club Green override */
    --radius: 0.75rem; /* 12px — alinhado ao design system */
}
```

### Customização de componentes shadcn

Quando um componente do shadcn precisar de variante específica do MyClub (ex: badge de conquista vs badge de plano), **não modificar o arquivo `ui/`** — criar um wrapper no domínio:

```ts
// resources/js/components/badge/AchievementBadge.vue
// Usa Badge do shadcn internamente + token --brand-victory-gold
```

### Tipografia

A fonte padrão configurada é **Manrope** (definida em `@theme inline` no `app.css`). A fonte precisa ser carregada via `<link>` no layout Blade ou via `@fontsource/manrope` no bundle:

```bash
npm install @fontsource/manrope
```

```ts
// resources/js/app.ts
import '@fontsource/manrope/400.css';
import '@fontsource/manrope/500.css';
import '@fontsource/manrope/600.css';
import '@fontsource/manrope/700.css';
```

### Referência dos arquivos de configuração

| Arquivo                             | Papel                                                    |
| ----------------------------------- | -------------------------------------------------------- |
| `resources/css/app.css`             | Tokens de tema, `@theme inline`, dark mode               |
| `components.json`                   | Configuração do shadcn-vue (estilo, aliases, ícones)     |
| `vite.config.ts`                    | Build (Tailwind v4 via `@tailwindcss/vite`)              |
| `resources/js/components/ui/`       | Primitivos do shadcn-vue — editáveis diretamente         |
| `resources/js/components/[domain]/` | Componentes de domínio que constroem sobre os primitivos |

---

## 17. Acessibilidade

Critérios mínimos:

- contraste adequado (ver pares WCAG em `docs/branding/visual-identity.md` §5)
- foco visível
- navegação por teclado
- labels associados corretamente
- feedback textual além de cor

### Regra crítica

Nenhuma informação importante pode depender apenas da cor.

---

## 19. i18n e conteúdo

Para entidades de catálogo e conteúdo sistêmico:

- backend fornece chaves estáveis
- frontend resolve tradução

### Regras

- não hardcodar texto de catálogo na UI quando existir `label_key`
- manter consistência entre cópia operacional e semântica de domínio

### Conteúdos sensíveis do produto

Precisam de cópia cuidadosamente diferenciada:

- convite de amistoso
- confirmação bilateral de resultado
- convite para elenco
- saída de jogador do time
- upgrade de plano
- destaque Player Pro
- premiação e badges

Não tratar esses fluxos como mensagens genéricas de CRUD.

---

## 19.1 Patrocínio e monetização na interface

Quando o produto incorporar patrocínio local ou AdSense em superfícies públicas:

- a interface deve manter o conteúdo principal soberano
- a identificação de patrocinador deve ser explícita
- os placements não podem competir com ações operacionais
- formulários, tabelas operacionais e autenticação continuam livres de anúncio

### Ordem de prioridade visual

1. ação principal do usuário
2. informação principal do domínio
3. feedback de sistema
4. destaque contextual
5. patrocínio / publicidade

---

## 20. Tokens de implementação

> Os tokens abaixo já estão implementados em `resources/css/app.css`. Esta seção serve como referência rápida. Ver `docs/branding/visual-identity.md` §5 para tabela completa com valores HSL e pares de contraste WCAG.

Exemplo de uso com as classes Tailwind geradas:

```css
/* Tokens de marca disponíveis como variáveis CSS */
.meu-componente {
    background: var(--brand-club-green); /* Club Green #126A43 */
    border-color: var(--brand-field-line); /* Field Line #DCE6DE */
    color: var(--brand-tape-white); /* Tape White #F5F7F3 */
}

/* Classes Tailwind geradas pelos tokens shadcn */
/* bg-primary → Club Green */
/* bg-accent → Victory Gold */
/* bg-sidebar → Pitch Dark */
/* text-muted-foreground → texto muted */
/* border → Field Line */
```

---

## 21. Checklist de revisão visual

Antes de considerar uma tela pronta, validar:

1. A ação principal está óbvia?
2. A hierarquia de informação está clara?
3. Os estados de feedback estão completos?
4. Os textos estão diretos e legíveis?
5. O módulo parece pertencer ao ecossistema visual do MyClub?
6. O contraste está suficiente?
7. O layout está funcional em desktop e mobile?
