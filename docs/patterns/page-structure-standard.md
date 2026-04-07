# Padrão de Estrutura de Páginas do Admin

## Objetivo

Definir uma estrutura de páginas administrativas por domínio desde o início do projeto, reduzindo refatorações estruturais futuras.

---

## Estrutura padrão

```text
resources/js/pages/admin/
└── [domain]/
    ├── Index.vue
    ├── Create.vue
    ├── Edit.vue
    ├── Show.vue
    └── Partials/
```

Exemplo:

```text
resources/js/pages/admin/teams/
├── Index.vue
├── Create.vue
├── Edit.vue
└── Partials/
    └── TeamForm.vue
```

---

## Regras

- novas páginas de domínio devem nascer dentro de `pages/admin/[domain]`
- usar `Partials/` quando a página precisar extrair blocos reutilizáveis
- tipos do domínio ficam em `resources/js/types/[domain]`
- componentes de domínio ficam em `resources/js/components/[domain]`

---

## Limites de complexidade

- páginas devem continuar legíveis e extraíveis
- se o `script setup` estiver ficando excessivo, extrair para composable
- se o template crescer demais, extrair para component/partial
- não usar a estrutura plana do starter para módulos novos do domínio

---

## Convenção de nomes

- arquivos em `PascalCase.vue`
- domínio em inglês
- folders em minúsculas

---

## Observação

As páginas nativas do starter podem continuar como estão enquanto não forem migradas. A regra vale principalmente para **novos módulos administrativos do domínio**.
