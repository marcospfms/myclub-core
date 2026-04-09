# Tests - Registro

Este arquivo registra cada teste criado, seu objetivo e cenarios cobertos.

---

## Resumo de Cobertura

| Modulo | Arquivos | Metodos | Cobertura | Status |
|--------|----------|---------|-----------|--------|
| Auth | 7 | 25 | 100% | ✅ Completo |
| Catalog Foundation | 7 | 29 | 100% | ✅ Completo |
| Phase 1 | 3 | 9 | 100% | ✅ Completo |
| Navigation/Core | 1 | 2 | 100% | ✅ Completo |
| Settings | 2 | 11 | 100% | ✅ Completo |
| Unit | 1 | 1 | 100% | ✅ Completo |
| **Total** | **21** | **77** | **100%** | ✅ |

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

### `tests/Feature/Catalog/CatalogModelAndServiceTest.php`
Valida enum, relacionamentos Eloquent e comportamento inicial dos services de catalogo.

| Metodo | Cenario |
|--------|---------|
| `test_sport_mode_model_loads_catalog_relationships` | `SportMode` carrega corretamente categorias, formacoes e posicoes vinculadas |
| `test_badge_type_scope_is_cast_to_enum` | `BadgeType.scope` e convertido para `BadgeScope` via cast |
| `test_badge_type_resource_returns_translation_keys_and_icon_key` | `BadgeTypeResource` retorna `label_key`, `description_key` e `icon` como contrato da API |
| `test_catalog_services_can_create_update_list_and_delete_basic_entities` | Services basicos executam CRUD de `Category`, `Position`, `Formation`, `StaffRole` e `BadgeType` |
| `test_sport_mode_service_can_create_update_and_sync_catalog_links` | `SportModeService` cria modalidade, sincroniza pivots e atualiza/remover corretamente |
| `test_seeded_sport_modes_positions_and_staff_roles_use_translation_keys_and_icon_keys` | Seeds usam `label_key`, `description_key` e `icon` como contrato estavel |

### `tests/Feature/Catalog/CatalogRequestAndResourceTest.php`
Valida Form Requests e API Resources dos catalogos.

| Metodo | Cenario |
|--------|---------|
| `test_store_requests_validate_catalog_creation_payloads` | Requests de criacao validam o contrato atual de catalogo, incluindo pivots e enum de badge |
| `test_update_requests_ignore_current_unique_values` | Requests de update ignoram o proprio registro nas regras `unique` |
| `test_catalog_resources_return_expected_payload_shapes` | Resources retornam payloads em `snake_case`, com campos e relacionamentos esperados |
| `test_catalog_write_requests_allow_only_admin_users` | Escrita de catalogo fica restrita a usuarios com `role = admin` |

### `tests/Feature/Catalog/CatalogRouteTest.php`
Valida rotas de catálogo na API e no painel admin.

| Metodo | Cenario |
|--------|---------|
| `test_catalog_api_routes_require_authentication` | Endpoints de catálogo da API exigem autenticação Sanctum |
| `test_authenticated_users_can_list_catalog_api_routes` | Usuário autenticado consegue listar todos os catálogos via API |
| `test_admin_catalog_routes_require_authentication` | Rotas administrativas de catálogo redirecionam visitante para login |
| `test_non_admin_users_cannot_access_admin_catalog_routes` | Usuário autenticado comum recebe 403 nas rotas admin de catálogo |
| `test_admin_users_can_access_admin_catalog_index_routes` | Usuário admin consegue acessar as listagens admin de catálogo |

### `tests/Feature/Catalog/AdminSportModeCrudTest.php`
Valida CRUD administrativo completo de modalidades esportivas.

| Metodo | Cenario |
|--------|---------|
| `test_admin_can_list_sport_modes` | Admin acessa a listagem Inertia de modalidades |
| `test_admin_can_render_create_sport_mode_screen` | Admin abre tela de criação com catálogos auxiliares carregados |
| `test_admin_can_create_sport_mode_with_catalog_links` | Admin cria modalidade e sincroniza categorias, formações e posições |
| `test_admin_can_render_edit_sport_mode_screen` | Admin abre tela de edição com vínculos carregados |
| `test_admin_can_update_sport_mode_and_sync_links` | Admin atualiza modalidade e ressincroniza pivôs |
| `test_admin_can_delete_sport_mode` | Admin remove modalidade do catálogo |
| `test_sport_mode_requires_required_fields` | Validação de obrigatoriedade no cadastro de modalidade |

### `tests/Feature/Catalog/AdminSimpleCatalogCrudTest.php`
Valida CRUD administrativo das entidades simples de catálogo.

| Metodo | Cenario |
|--------|---------|
| `test_admin_can_crud_categories` | Admin lista, cria, edita e exclui categorias |
| `test_admin_can_crud_positions` | Admin lista, cria, edita e exclui posições |
| `test_admin_can_crud_formations` | Admin lista, cria, edita e exclui formações |
| `test_admin_can_crud_staff_roles` | Admin lista, cria, edita e exclui funções da comissão |
| `test_admin_can_crud_badge_types` | Admin lista, cria, edita e exclui tipos de badge |

### `tests/Feature/Catalog/CatalogApiResponseTest.php`
Valida o contrato de resposta da API de catálogo.

| Metodo | Cenario |
|--------|---------|
| `test_sport_modes_api_returns_nested_catalog_payload` | API de modalidades retorna payload aninhado com categorias, formações e posições |
| `test_simple_catalog_endpoints_return_expected_payload_shapes` | Endpoints simples retornam envelope e shape esperados em `snake_case` |

---

## Navigation/Core

### `tests/Feature/DashboardTest.php`
Valida acesso ao dashboard administrativo.

| Metodo | Cenario |
|--------|---------|
| `test_guests_are_redirected_to_the_login_page` | Visitante nao autenticado vai para login |
| `test_authenticated_users_can_visit_the_dashboard` | Usuario autenticado acessa o dashboard |

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

## Phase 1

### `tests/Feature/Phase1/PlayerApiTest.php`
Valida a API de perfil esportivo do jogador.

| Metodo | Cenario |
|--------|---------|
| `test_player_endpoints_require_authentication` | Endpoints de criação, atualização e visualização de player exigem autenticação |
| `test_authenticated_user_can_create_and_update_own_player_profile` | Usuário autenticado cria e atualiza o próprio perfil esportivo |
| `test_player_show_hides_sensitive_fields_from_other_authenticated_users` | CPF, RG e telefone não são expostos para outro usuário autenticado |

### `tests/Feature/Phase1/TeamApiTest.php`
Valida a API de times e modalidades vinculadas ao time.

| Metodo | Cenario |
|--------|---------|
| `test_authenticated_user_can_create_list_update_and_deactivate_owned_teams` | Dono cria, lista, atualiza e desativa seus próprios times |
| `test_team_show_is_public_and_team_update_is_restricted_to_owner_or_admin` | Perfil do time é público, mas atualização é restrita a owner ou admin |
| `test_owner_can_add_and_remove_team_sport_mode_and_conflict_returns_domain_error` | Dono adiciona/remove modalidade e conflitos retornam erro de domínio |

### `tests/Feature/Phase1/TeamInvitationAndRosterApiTest.php`
Valida os fluxos de convite e elenco.

| Metodo | Cenario |
|--------|---------|
| `test_owner_can_send_invitation_and_invited_user_can_accept_it` | Dono envia convite e atleta convidado aceita, gerando membership |
| `test_public_roster_is_visible_and_player_can_leave_team` | Elenco é público e o próprio jogador pode sair do time |
| `test_owner_can_remove_member_and_conflicts_return_409` | Dono remove membro do elenco e conflitos de convite/modalidade retornam 409 |

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
- form requests de catalogo
- api resources de catalogo
- rotas autenticadas de catalogo na API e no admin
- CRUD administrativo de catálogo via Inertia
- contrato HTTP da API de catálogo
- API de perfis de jogador da Fase 1
- API de times, modalidades e ownership da Fase 1
- API de convites e elenco da Fase 1

---

## Observacoes

- A suite atual esta concentrada em autenticacao, settings e fundacao inicial do dominio.
- Os proximos blocos do roadmap devem adicionar testes para:
  - aprofundar cenarios de comissão técnica
  - limites de plano e feature gating no domínio esportivo
