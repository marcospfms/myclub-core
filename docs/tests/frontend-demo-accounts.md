# Frontend Demo Accounts

Credenciais de seed local para navegar no frontend do `myclub-core`.

Todas as contas usam a senha:

```text
teste123
```

## Contas

| Email | Papel | O que validar |
| --- | --- | --- |
| `admin@myclub.app` | Admin global e organizador | Acesso administrativo, visão ampla e campeonato arquivado |
| `organizador@myclub.app` | Organizador de campeonatos | Campeonatos em `draft`, `enrollment`, `active`, `finished` e `cancelled` |
| `dono.alpha@myclub.app` | Dono de time | Gestão do time `Lobos FC` e navegação em modalidades `campo` e `society` |
| `dono.beta@myclub.app` | Dono de time | Gestão do time `Estrela Azul` e inscrição em campeonatos |
| `misto.carlos@myclub.app` | Dono de time + jogador | Gestão do `Racha Central` e navegação como atleta do próprio time |
| `misto.marina@myclub.app` | Dona de time + jogadora | Gestão do `Cidade Nova` e navegação como atleta do próprio time |
| `jogador.lucas@myclub.app` | Jogador puro com histórico público | Perfil esportivo, vínculos ativos e participação em elencos |
| `jogador.beatriz@myclub.app` | Jogadora pura com convite pendente | Perfil esportivo e fluxo de convite para entrar em time |
| `comissao.renato@myclub.app` | Comissão técnica | Leitura contextual de elenco e estatísticas do time vinculado |

## Cenários seeded

### Times

- `Lobos FC`
- `Estrela Azul`
- `Racha Central`
- `Cidade Nova`

### Campeonatos

- `Liga Demo Draft 2026`
- `Liga Demo Inscricoes 2026`
- `Liga Demo Ativa 2026`
- `Liga Demo Finalizada 2025`
- `Liga Demo Arquivada 2024`
- `Liga Demo Cancelada 2026`

### Expectativas de navegação

- O organizador comum deve enxergar campeonatos em estados diferentes.
- Os donos de time devem ter times reais com elencos e modalidades associadas.
- Os perfis mistos devem aparecer como dono de time e como jogador.
- A conta `jogador.beatriz@myclub.app` deve ter um convite pendente para testar o fluxo de entrada em elenco.
- O admin deve conseguir validar contexto administrativo e histórico arquivado.
