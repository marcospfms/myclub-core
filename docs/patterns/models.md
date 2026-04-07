# Models

## Objetivo

Definir padrões de modelagem para classes Eloquent com foco em clareza, consistencia e manutencao.

## Estrutura minima recomendada

- namespace coerente
- declaracao explicita de tabela quando nao seguir pluralizacao padrao
- nomes de classes e tabelas em ingles
- `$fillable` ou `$guarded` bem definido
- `casts()` ou `$casts` para tipos relevantes
- relacionamentos tipados

## Regras de clean code

- um model representa uma entidade ou agregado de dados coerente
- nao misturar regra de negocio complexa no model quando isso pertencer a um service
- manter nomes de relacao sem ambiguidades
- evitar helpers magicos sem necessidade
- nao modelar o schema novo como copia literal do banco legado
- documentar apenas o que nao for obvio pelo codigo

## Nomenclatura

- arquivo: `PascalCase.php`
- classe: mesmo nome do arquivo
- relacoes `belongsTo`: singular
- relacoes `hasMany`: plural
- pivots: nome alfabetico em `snake_case`

## Exemplo generico

```php
class Resource extends Model
{
    protected $table = 'resource';

    protected $fillable = [
        'owner_id',
        'name',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ResourceItem::class);
    }
}
```

## Checklist de revisao

- campos mass assignable estao corretos
- casts cobrem json, booleans, decimais e datas relevantes
- relacionamentos estao tipados
- nomes expressam intencao
- o model nao descreve modulos externos ou legados sem necessidade
- o model segue o novo dominio em ingles
