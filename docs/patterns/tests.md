# Tests

## Objetivo
Padronizar testes automatizados para garantir cobertura util e manutencao simples.

## Regras gerais
- nomes de teste devem descrever comportamento
- cada arquivo deve cobrir um contexto claro
- usar `RefreshDatabase` apenas quando houver persistencia real
- evitar testes boilerplate sem valor
- remover testes de fluxos aposentados quando o modulo sair do produto

## Convencoes de nomenclatura
- arquivo: `ResourceTest.php`
- metodos:
  - `test_can_list_resources()`
  - `test_can_create_resource()`
  - `test_cannot_access_other_user_resource()`
  - `test_requires_authentication()`

## Estrutura recomendada
### Feature
- valida contrato HTTP
- valida autorizacao e escopo
- valida efeitos em banco
- diferencia API JSON de admin Inertia quando aplicavel

### Unit
- valida regra isolada
- nao deve depender de banco quando isso nao for necessario
- usa doubles, mocks ou fakes quando apropriado

## Exemplo generico
```php
class ResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_resource(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/resources', [
            'name' => 'Example',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('resource', [
            'name' => 'Example',
        ]);
    }
}
```

## Checklist de revisao
- existe happy path
- existe cobertura de validacao
- existe cobertura de autorizacao
- assertions verificam comportamento real
- o teste nao depende de detalhes acidentais do framework
- endpoints de API validam envelope e formato `snake_case` quando aplicavel
