# Services

## Objetivo

`Services` são a camada padrão de aplicação e negócio do `myclub-core`.

Neste projeto:

- usar `Services` como padrão único
- não adotar `Actions`
- não adotar `Queries`

Mesmo operações bem definidas e pontuais devem ficar dentro do `Service` do contexto apropriado.

---

## Quando usar Service

Use `Service` para:

- regras de negócio
- criação, atualização e remoção de entidades
- consultas compostas relevantes ao domínio
- transações
- sincronizações entre agregados
- integrações externas
- validações de negócio que vão além do `FormRequest`

---

## Quando não usar Service

Não use service para:

- validação estrutural de input do HTTP
- serialização de saída
- regras puramente visuais de frontend

---

## Organização recomendada

```text
app/Services/
├── Auth/
├── Teams/
├── Players/
├── Championships/
└── FriendlyMatches/
```

Exemplo:

```text
app/Services/Teams/TeamService.php
```

---

## Regra prática

- um service pode ter vários métodos, desde que pertençam ao mesmo contexto
- não quebrar um contexto em vários artefatos artificiais só porque uma operação é “bem definida”
- se um service crescer demais, dividir por subcontexto e não por padrão técnico

---

## Exemplo

```php
class TeamService
{
    public function create(array $data, User $actor): Team
    {
        // business rules
    }

    public function update(Team $team, array $data, User $actor): Team
    {
        // business rules
    }

    public function delete(Team $team, User $actor): void
    {
        // business rules
    }

    public function listForAdmin(array $filters = []): LengthAwarePaginator
    {
        // domain query rules
    }
}
```

---

## Relação com controllers

- controller chama o service
- service retorna entidades, coleções ou resultados estruturados
- API controller transforma isso com Resource
- web/admin controller transforma isso em props para Inertia

---

## Checklist de revisão

- lógica de negócio está fora do controller
- o service pertence a um contexto claro
- nomes estão em inglês
- não houve criação desnecessária de Action/Query
- transações estão encapsuladas quando necessário
