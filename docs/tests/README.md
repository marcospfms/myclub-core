# Tests - Registro

Este arquivo registra cada teste criado, seu objetivo e cenarios cobertos.

---

## Resumo de Cobertura

| Modulo | Arquivos | Metodos | Cobertura | Status |
|--------|----------|---------|-----------|--------|
| Auth | 7 | 25 | 100% | ✅ Completo |
| Catalog Foundation | 1 | 2 | 100% | ✅ Completo |
| Navigation/Core | 2 | 3 | 100% | ✅ Completo |
| Settings | 2 | 11 | 100% | ✅ Completo |
| Unit | 1 | 1 | 100% | ✅ Completo |
| **Total** | **13** | **42** | **100%** | ✅ |

---

## Auth

### `tests/Feature/Auth/AuthenticationTest.php`
Valida login, logout, 2FA e rate limit do fluxo de autenticacao.

| Metodo | Cenario |
|--------|---------|
| `test_login_screen_can_be_rendered` | Tela de login responde com sucesso |
| `test_users_can_authenticate_using_the_login_screen` | Credenciais validas autenticam e redirecionam para dashboard |
| `test_users_with_two_factor_enabled_are_redirected_to_two_factor_challenge` | Usuario com 2FA ativo vai para desafio de segundo fator |
| `test_users_can_not_authenticate_with_invalid_password` | Senha incorreta nao autentica |
| `test_users_can_logout` | Logout invalida a sessao e redireciona para home |
| `test_users_are_rate_limited` | Excesso de tentativas retorna 429 |

### `tests/Feature/Auth/EmailVerificationTest.php`
Cobre verificacao de email e protecoes do link assinado.

| Metodo | Cenario |
|--------|---------|
| `test_email_verification_screen_can_be_rendered` | Usuario nao verificado acessa a tela de verificacao |
| `test_email_can_be_verified` | Link assinado valido verifica o email e dispara evento |
| `test_email_is_not_verified_with_invalid_hash` | Hash invalido nao verifica o email |
| `test_email_is_not_verified_with_invalid_user_id` | ID invalido nao verifica o email |
| `test_verified_user_is_redirected_to_dashboard_from_verification_prompt` | Usuario ja verificado nao fica preso no prompt |
| `test_already_verified_user_visiting_verification_link_is_redirected_without_firing_event_again` | Link repetido nao dispara evento novamente |

### `tests/Feature/Auth/PasswordConfirmationTest.php`
Valida confirmacao de senha para rotas sensiveis.

| Metodo | Cenario |
|--------|---------|
| `test_confirm_password_screen_can_be_rendered` | Usuario autenticado acessa a tela de confirmacao |
| `test_password_confirmation_requires_authentication` | Visitante e redirecionado para login |

### `tests/Feature/Auth/PasswordResetTest.php`
Valida o fluxo de recuperacao e redefinicao de senha.

| Metodo | Cenario |
|--------|---------|
| `test_reset_password_link_screen_can_be_rendered` | Tela de solicitar reset responde com sucesso |
| `test_reset_password_link_can_be_requested` | Solicitacao envia notificacao de reset |
| `test_reset_password_screen_can_be_rendered` | Token valido abre a tela de redefinicao |
| `test_password_can_be_reset_with_valid_token` | Token valido redefine a senha e redireciona para login |
| `test_password_cannot_be_reset_with_invalid_token` | Token invalido retorna erro de sessao |

### `tests/Feature/Auth/RegistrationTest.php`
Valida o cadastro de novos usuarios.

| Metodo | Cenario |
|--------|---------|
| `test_registration_screen_can_be_rendered` | Tela de registro responde com sucesso |
| `test_new_users_can_register` | Cadastro valido cria usuario autenticado |

### `tests/Feature/Auth/TwoFactorChallengeTest.php`
Valida a tela de desafio de dois fatores.

| Metodo | Cenario |
|--------|---------|
| `test_two_factor_challenge_redirects_to_login_when_not_authenticated` | Sem fluxo de login parcial, rota redireciona para login |
| `test_two_factor_challenge_can_be_rendered` | Usuario com 2FA ativo recebe o componente Inertia correto |

### `tests/Feature/Auth/VerificationNotificationTest.php`
Valida reenvio de verificacao de email.

| Metodo | Cenario |
|--------|---------|
| `test_sends_verification_notification` | Usuario nao verificado recebe novo email de verificacao |
| `test_does_not_send_verification_notification_if_email_is_verified` | Usuario ja verificado nao recebe notificacao |

---

## Catalog Foundation

### `tests/Feature/Catalog/CatalogSetupTest.php`
Valida a fundacao da Fase 0 para catalogos e dados de referencia.

| Metodo | Cenario |
|--------|---------|
| `test_catalog_tables_are_created` | Todas as 6 tabelas de catalogo e 3 pivots existem apos as migrations |
| `test_catalog_seeders_populate_reference_data` | Seeders populam quantidades corretas e exemplos criticos de modalidades, posicoes e badges |

---

## Navigation/Core

### `tests/Feature/DashboardTest.php`
Valida acesso ao dashboard administrativo.

| Metodo | Cenario |
|--------|---------|
| `test_guests_are_redirected_to_the_login_page` | Visitante nao autenticado vai para login |
| `test_authenticated_users_can_visit_the_dashboard` | Usuario autenticado acessa o dashboard |

### `tests/Feature/ExampleTest.php`
Teste basico de sanidade da home.

| Metodo | Cenario |
|--------|---------|
| `test_returns_a_successful_response` | Rota base `home` responde com sucesso |

---

## Settings

### `tests/Feature/Settings/ProfileUpdateTest.php`
Valida exibicao e alteracoes do perfil do usuario.

| Metodo | Cenario |
|--------|---------|
| `test_profile_page_is_displayed` | Usuario autenticado acessa a pagina de perfil |
| `test_profile_information_can_be_updated` | Nome e email podem ser atualizados |
| `test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged` | Mesmo email nao limpa verificacao |
| `test_user_can_delete_their_account` | Usuario pode excluir a propria conta com senha correta |
| `test_correct_password_must_be_provided_to_delete_account` | Senha incorreta bloqueia exclusao |

### `tests/Feature/Settings/SecurityTest.php`
Valida pagina de seguranca, 2FA e troca de senha.

| Metodo | Cenario |
|--------|---------|
| `test_security_page_is_displayed` | Pagina de seguranca renderiza com dados esperados de 2FA |
| `test_security_page_requires_password_confirmation_when_enabled` | Confirmacao de senha e exigida quando configurada |
| `test_security_page_does_not_require_password_confirmation_when_disabled` | Sem confirmacao exigida, pagina abre normalmente |
| `test_security_page_renders_without_two_factor_when_feature_is_disabled` | Pagina continua funcional sem a feature de 2FA |
| `test_password_can_be_updated` | Senha pode ser alterada com senha atual correta |
| `test_correct_password_must_be_provided_to_update_password` | Senha atual incorreta bloqueia a troca |

---

## Unit

### `tests/Unit/ExampleTest.php`
Teste unitario placeholder da suite.

| Metodo | Cenario |
|--------|---------|
| `test_that_true_is_true` | Assercao trivial de sanidade da suite unitária |

---

## Regras Cobertas Atualmente

As principais regras cobertas hoje pela suite sao:

- autenticacao por email e senha
- logout e rate limit de login
- fluxo de 2FA com redirecionamento correto
- verificacao de email com link assinado
- recuperacao e redefinicao de senha
- cadastro de usuario
- protecao de dashboard para usuarios autenticados
- exibicao e alteracao de perfil
- exclusao de conta com confirmacao de senha
- pagina de seguranca com comportamento dependente da configuracao do Fortify
- fundacao da Fase 0: migrations e seeders de catalogo

---

## Observacoes

- A suite atual esta concentrada em autenticacao, settings e fundacao inicial do dominio.
- Os proximos blocos do roadmap devem adicionar testes para:
  - models de catalogo
  - services de catalogo
  - API autenticada de leitura dos catalogos
  - CRUD administrativo via Inertia
  - regras de autorizacao do dominio esportivo
