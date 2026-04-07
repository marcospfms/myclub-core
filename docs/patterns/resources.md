# Resources

## Objetivo

Padronizar transformacoes de resposta para APIs JSON.

> Este documento vale para a **API pública**. Responses Inertia/web nao usam `JsonResource`.

## Regras gerais

- expor apenas os campos necessarios
- usar `snake_case` nas chaves
- incluir timestamps quando fizer sentido para o contrato
- usar `whenLoaded()` para relacionamentos
- nunca expor segredos, tokens ou metadados internos sem necessidade funcional

## Boas praticas

- resources devem transformar dados, nao executar regra de negocio complexa
- campos derivados simples podem ser calculados no resource
- colecoes devem manter consistencia de formato com recursos individuais
- decimais sensiveis devem ser serializados de forma previsivel

## Exemplo generico

```php
class ResourceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'owner' => OwnerResource::make($this->whenLoaded('owner')),
        ];
    }
}
```

## Checklist de revisao

- chaves em `snake_case`
- sem campos sensiveis
- sem dependencia desnecessaria de relacoes nao carregadas
- sem acoplamento a detalhes de infraestrutura
