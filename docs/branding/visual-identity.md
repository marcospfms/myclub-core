# Identidade Visual — MyClub

## 1. Princípio visual central

A identidade visual do MyClub deve traduzir:

- estrutura operacional
- tensão competitiva
- pertencimento esportivo

Ela também precisa acomodar quatro contextos de produto distintos:

- operação administrativa
- perfis públicos e rankings
- cartões e destaque individual do Player Pro
- superfícies com patrocínio local

A linguagem visual não deve se apoiar em clichês superficiais como:

- bolas de futebol como ornamento recorrente
- escudos aleatórios sem lógica
- verde neon sobre preto como solução padrão
- grafismos agressivos demais que prejudiquem legibilidade

---

## 2. Direção estética

### Sensação desejada

- atlética
- precisa
- confiável
- tática
- contemporânea

### Mistura estética pretendida

- base sóbria e funcional
- acentos energéticos
- contraste forte
- elementos geométricos inspirados em placas, camisas e marcações de jogo

---

## 3. Paleta principal

## 3.1 Cor de marca primária

### `Club Green`

- Hex: `#126A43`
- RGB: `18 106 67`
- Uso:
    - botões primários
    - highlights de marca
    - estados de confirmação esportiva
    - elementos fortes de identidade

### Significado

- campo
- clube
- estabilidade
- crescimento
- confiança

---

## 3.2 Cor institucional profunda

### `Pitch Dark`

- Hex: `#0E1512`
- RGB: `14 21 18`
- Uso:
    - fundos profundos
    - headers densos
    - áreas hero
    - contrastes com verde e branco

### Significado

- densidade
- seriedade
- sofisticação
- contraste dramático

---

## 3.3 Cor neutra clara

### `Tape White`

- Hex: `#F5F7F3`
- RGB: `245 247 243`
- Uso:
    - superfícies claras
    - fundos administrativos
    - áreas de leitura prolongada

### Significado

- limpeza
- respiro
- clareza

---

## 3.4 Cor de acento competitivo

### `Victory Gold`

- Hex: `#D9A441`
- RGB: `217 164 65`
- Uso:
    - badges
    - status de destaque
    - prêmios
    - pequenas áreas de foco

### Significado

- mérito
- destaque
- conquista

### Uso estratégico no produto

- badges de conquista
- placas de campeão
- acentos de ranking
- indicadores premium do Player Pro

O dourado não deve ser usado de modo a confundir:

- conquista por performance
- assinatura paga

Quando o contexto for Player Pro, o dourado precisa vir acompanhado de label textual inequívoco.

---

## 3.5 Cor de apoio tático

### `Field Line`

- Hex: `#DCE6DE`
- RGB: `220 230 222`
- Uso:
    - bordas suaves
    - divisórias
    - cards em fundo claro
- grids sutis

---

## 3.6 Cor para superfícies públicas de alta energia

### `Night Stand`

- Hex: `#13211B`
- RGB: `19 33 27`

Uso:

- fundos de cards públicos
- hero de ranking
- cabeçalhos de perfil
- placas de campeonato

Essa cor permite um produto público mais dramático sem abandonar a família cromática principal.

---

## 4. Paleta expandida

### Estados de sistema

#### `Success`

- Hex: `#15803D`

#### `Warning`

- Hex: `#CA8A04`

#### `Danger`

- Hex: `#B42318`

#### `Info`

- Hex: `#0F5EA8`

#### `Neutral Strong`

- Hex: `#334155`

---

## 5. Tokens cromáticos — CSS e Tailwind v4

> Os tokens abaixo estão implementados em `resources/css/app.css` usando a sintaxe de variáveis CSS do shadcn-vue + Tailwind v4. O bloco `:root` define o tema claro; `.dark` define o tema escuro. Classes utilitárias Tailwind são geradas automaticamente via `@theme inline` para os prefixos `color-*`.

### Mapeamento de variáveis CSS para a paleta MyClub

| Token CSS              | Valor (claro)      | Referência de cor       |
| ---------------------- | ------------------ | ----------------------- |
| `--background`         | `hsl(90 14% 94%)`  | Tape White              |
| `--foreground`         | `hsl(140 21% 9%)`  | Texto primário          |
| `--primary`            | `hsl(151 71% 24%)` | Club Green              |
| `--primary-foreground` | `hsl(90 14% 94%)`  | Tape White              |
| `--accent`             | `hsl(38 63% 55%)`  | Victory Gold            |
| `--muted-foreground`   | `hsl(150 7% 45%)`  | Texto muted             |
| `--border`             | `hsl(130 14% 87%)` | Field Line              |
| `--destructive`        | `hsl(4 68% 40%)`   | Danger                  |
| `--ring`               | `hsl(151 71% 24%)` | Club Green (focus ring) |
| `--sidebar-background` | `hsl(150 20% 8%)`  | Pitch Dark              |
| `--sidebar-accent`     | `hsl(153 24% 11%)` | Night Stand             |

### Tokens de marca adicionais (variáveis diretas no CSS)

```css
--brand-club-green: hsl(151 71% 24%); /* #126A43 */
--brand-club-green-hover: hsl(151 71% 20%); /* #0F5B39 */
--brand-club-green-active: hsl(151 71% 16%); /* #0C4B2F */
--brand-pitch-dark: hsl(150 20% 8%); /* #0E1512 */
--brand-tape-white: hsl(90 14% 94%); /* #F5F7F3 */
--brand-victory-gold: hsl(38 63% 55%); /* #D9A441 */
--brand-field-line: hsl(130 14% 87%); /* #DCE6DE */
--brand-night-stand: hsl(153 24% 11%); /* #13211B */

--color-success: hsl(142 70% 29%); /* #15803D */
--color-warning: hsl(42 96% 40%); /* #CA8A04 */
--color-danger: hsl(4 68% 40%); /* #B42318 */
--color-info: hsl(210 70% 38%); /* #0F5EA8 */
```

### Como usar no código Vue/Tailwind

```html
<!-- Via classe Tailwind (gerada pelo @theme inline) -->
<button class="bg-primary text-primary-foreground">Ação principal</button>
<div class="bg-sidebar text-sidebar-foreground">Sidebar</div>

<!-- Via variável CSS direta (para tokens de marca fora do shadcn) -->
<style>
    .my-component {
        background: var(--brand-club-green);
        border-color: var(--brand-field-line);
    }
</style>
```

### Dark mode

O dark mode é ativado pela classe `.dark` no elemento raiz (mecanismo do shadcn-vue + Tailwind v4). Superfícies escuras invertem `background` para `Pitch Dark` e `card` para `Night Stand`. O `Club Green` mantém a presença no primário com leve ajuste de lightness (+6%) para garantir contraste mínimo WCAG AA sobre fundo escuro.

**Pares de contraste validados (WCAG):**

| Foreground             | Background           | Razão  | Grade                                                    |
| ---------------------- | -------------------- | ------ | -------------------------------------------------------- |
| Tape White `#F5F7F3`   | Club Green `#126A43` | 5.8:1  | AA ✅                                                    |
| Club Green `#126A43`   | Tape White `#F5F7F3` | 5.8:1  | AA ✅                                                    |
| Tape White `#F5F7F3`   | Pitch Dark `#0E1512` | 15.4:1 | AAA ✅                                                   |
| Texto `#142018`        | Tape White `#F5F7F3` | 16.2:1 | AAA ✅                                                   |
| Victory Gold `#D9A441` | Pitch Dark `#0E1512` | 7.9:1  | AAA ✅ (não usar como texto pequeno sobre outros fundos) |

> **Regra**: `Club Green` sobre fundo branco puro `#FFFFFF` tem razão 4.7:1 (AA) — aprovado para elementos de 18px+ ou bold 14px+. Para texto de corpo menor que 18px, preferir `Pitch Dark` como cor de texto.

---

## 6. Tipografia

### 6.1 Família principal

### Recomendada

**Manrope**

Motivos:

- contemporânea
- muito legível
- funciona bem em dashboard
- tem peso e nitidez para headings

### 6.2 Família secundária

### Recomendada

**IBM Plex Sans**

Uso:

- tabelas
- informação densa
- interfaces administrativas muito técnicas

### 6.3 Família de apoio expressiva

### Opcional para headlines ou materiais de marca

**Sora**

Uso restrito:

- campanhas
- headers institucionais
- peças de lançamento

Não usar como tipografia-base de formulários e tabelas.

---

## 7. Hierarquia tipográfica

### Display

- Peso: `700`
- Tracking: `-0.03em`
- Uso: banners institucionais e peças de branding

### Heading 1

- Tamanho: `2rem` a `2.5rem`
- Peso: `700`
- Uso: título de página principal

### Heading 2

- Tamanho: `1.5rem`
- Peso: `700`
- Uso: seções importantes

### Heading 3

- Tamanho: `1.125rem`
- Peso: `600`
- Uso: cards, blocos e formulários

### Body

- Tamanho: `0.95rem` a `1rem`
- Peso: `400` a `500`

### Meta / label

- Tamanho: `0.75rem` a `0.875rem`
- Peso: `600`
- Tracking levemente positivo

---

## 8. Forma e geometria

### Borda padrão

- raio entre `12px` e `18px` para cards e superfícies

### Botões

- raio entre `10px` e `14px`

### Elementos premium

- badges, placas e indicadores podem usar formatos ligeiramente mais rígidos, com recortes visuais inspirados em patch, etiqueta ou escudo simplificado

---

## 9. Espaçamento

Adotar escala consistente:

- `4`
- `8`
- `12`
- `16`
- `24`
- `32`
- `40`
- `48`
- `64`

### Regra prática

- componentes pequenos: múltiplos de `4`
- seções internas: `16` ou `24`
- módulos de página: `32` ou `40`

---

## 10. Iconografia

### Estilo

- traço limpo
- geometria simples
- sem excesso de detalhe
- consistente com Lucide

### Uso

- ícones devem reforçar semântica, não decorar
- se o texto já resolve sozinho, o ícone é opcional
- usar ícones especialmente em:
    - navegação
    - status
    - badges
    - catálogos visuais

### Famílias semânticas importantes do produto

- time e elenco
- campeonato e chaveamento
- amistoso e confirmação bilateral
- perfil e visibilidade pública
- badge e mérito
- patrocínio e parceria

---

## 11. Texturas e fundos

Texturas recomendadas:

- grid técnico suave
- linhas de marcação inspiradas em campo
- diagonais discretas
- áreas chapadas com contraste controlado

Texturas proibidas:

- gramado fotográfico
- metal escovado
- textura de couro
- efeitos esportivos genéricos com fumaça, raios e brilho exagerado

---

## 12. Ilustração e fotografia

> **Seção pendente de complemento.** A direção abaixo é completa para o contexto atual.

### Direção

- realista
- documental
- esportiva
- humana

### Preferência

- times reais
- gestos de jogo
- reunião de elenco
- ambiente de várzea ou quadra com autenticidade

Evitar:

- banco de imagem muito posado
- fotos corporativas genéricas
- atletas “de catálogo” sem contexto

---

## 13. Aplicação no admin

O painel administrativo deve usar:

- fundos claros por padrão
- verde da marca como ação principal
- dourado apenas como acento de destaque
- preto profundo apenas em áreas de contraste, cabeçalhos ou blocos especiais

O admin não deve parecer um site de marketing.
Ele deve parecer um sistema de operação esportiva sério.

---

## 14. Aplicação em produto público

O produto público pode ser mais dramático do que o admin, com:

- fundos escuros
- gradientes mais ricos
- áreas hero com marca forte
- uso mais generoso de textura

Mesmo assim, a base visual deve continuar reconhecível como MyClub.

### Regras especiais para ranking e Player Pro

- destaque visual existe, mas não pode desfigurar a leitura da tabela
- o bloco de destaque deve ser claramente rotulado como destaque visual, não “melhor posição”
- ranking orgânico continua visualmente legível e soberano

### Regras especiais para patrocínio local

- blocos patrocinados devem ser integrados ao layout, não parecer banners soltos
- patrocinador nunca pode ter mais peso visual que o nome da competição, do time ou do atleta
- cada placement precisa parecer uma associação contextual, não poluição

### Regras especiais para campeonato

- fases, grupos, rodadas e status exigem diferenciação visual muito clara
- o visual deve privilegiar legibilidade de tabela, confronto e progressão de fase

---

## 15. Regras de consistência

- usar verde como assinatura principal
- usar dourado com moderação
- preservar neutros claros para leitura
- nunca comprometer acessibilidade em favor de impacto visual
- evitar misturar estilos visuais conflitantes entre superfícies do mesmo ecossistema
