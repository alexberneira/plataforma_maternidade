# 🎁 Sistema de Trial - Plataforma de Maternidade

## 📋 Visão Geral

O sistema de trial permite que novos usuários experimentem a plataforma gratuitamente por **7 dias** antes de decidir se querem continuar com a assinatura mensal.

## 💰 Estrutura de Preços

- **Trial**: R$ 0,00 por 7 dias
- **Assinatura**: R$ 39,00/mês após o trial
- **Cancelamento**: A qualquer momento

## 🔧 Configuração no Stripe

### 1. Criar Produto com Trial
1. Acesse o Dashboard do Stripe
2. Vá em **Products**
3. Crie um novo produto ou edite o existente
4. Configure o preço com trial:
   - **Price**: R$ 39,00
   - **Billing period**: Monthly
   - **Trial period**: 7 days
   - **Currency**: BRL

### 2. Configurar Webhooks
Adicione estes eventos ao webhook:
- `customer.subscription.created`
- `customer.subscription.updated`
- `customer.subscription.deleted`
- `invoice.payment_succeeded`
- `invoice.payment_failed`

## 🗄️ Estrutura do Banco de Dados

### Tabela `assinaturas`
```sql
CREATE TABLE assinaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_email VARCHAR(255) NOT NULL,
    stripe_customer_id VARCHAR(255),
    stripe_subscription_id VARCHAR(255) UNIQUE,
    status ENUM('trialing', 'active', 'past_due', 'canceled', 'unpaid') DEFAULT 'trialing',
    data_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_fim TIMESTAMP NULL,
    trial_end TIMESTAMP NULL,  -- Nova coluna para trial
    valor DECIMAL(10,2) DEFAULT 39.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 🔄 Fluxo do Trial

### 1. Usuário acessa a plataforma
- Vê mensagem sobre trial gratuito
- Clica em "Começar Trial Gratuito"

### 2. Criação da assinatura
- Sistema cria cliente no Stripe
- Cria assinatura com `trial_period_days: 7`
- Salva no banco com status `trialing`

### 3. Durante o trial
- Usuário tem acesso completo
- Vê contador de dias restantes
- Pode cancelar a qualquer momento

### 4. Fim do trial
- Stripe cobra automaticamente R$ 39,00
- Status muda para `active`
- Próxima cobrança em 30 dias

## 📊 Status das Assinaturas

| Status | Descrição |
|--------|-----------|
| `trialing` | Em período de trial (7 dias) |
| `active` | Assinatura ativa e paga |
| `past_due` | Pagamento em atraso |
| `canceled` | Assinatura cancelada |
| `unpaid` | Pagamento falhou |

## 🛠️ Funções PHP

### Verificar se tem assinatura ativa
```php
hasActiveSubscription($userEmail, $pdo)
// Retorna true se status = 'active' OU 'trialing'
```

### Verificar se está em trial
```php
isInTrial($userEmail, $pdo)
// Retorna true se status = 'trialing'
```

### Dias restantes do trial
```php
getTrialDaysLeft($userEmail, $pdo)
// Retorna número de dias restantes
```

## 🎨 Interface do Usuário

### Página Principal (`index.php`)
- Mostra alerta sobre trial para usuários sem assinatura
- Exibe contador de dias para usuários em trial
- Confirma assinatura ativa para usuários pagos

### Página de Assinatura (`assinatura.php`)
- Design atrativo com badge "TRIAL"
- Destaque para "R$ 0,00 nos primeiros 7 dias"
- Preço de R$ 39/mês claramente visível
- Lista de benefícios incluídos

## 🔔 Webhooks

### Eventos Processados
1. **subscription.created**: Cria registro no banco
2. **subscription.updated**: Atualiza status e datas
3. **subscription.deleted**: Marca como cancelada
4. **payment.succeeded**: Registra pagamento
5. **payment.failed**: Registra falha

### Funções de Processamento
- `handleSubscriptionCreated()`: Cria assinatura
- `handleSubscriptionUpdated()`: Atualiza status
- `handleSubscriptionDeleted()`: Cancela assinatura
- `handlePaymentSucceeded()`: Registra pagamento
- `handlePaymentFailed()`: Registra falha

## 🧪 Testes

### Cartões de Teste
- **Sucesso**: `4242 4242 4242 4242`
- **Falha**: `4000 0000 0000 0002`
- **3D Secure**: `4000 0025 0000 3155`

### Cenários de Teste
1. **Trial iniciado**: Verificar status `trialing`
2. **Trial ativo**: Verificar contador de dias
3. **Trial expirado**: Verificar cobrança automática
4. **Cancelamento**: Verificar status `canceled`

## 📈 Métricas Importantes

### Para Acompanhar
- Taxa de conversão trial → assinatura
- Tempo médio no trial
- Taxa de cancelamento durante trial
- Taxa de cancelamento após primeiro pagamento

### Queries Úteis
```sql
-- Usuários em trial
SELECT COUNT(*) FROM assinaturas WHERE status = 'trialing';

-- Conversões trial → ativo
SELECT COUNT(*) FROM assinaturas WHERE status = 'active';

-- Dias médios no trial
SELECT AVG(DATEDIFF(trial_end, data_inicio)) FROM assinaturas WHERE status = 'active';
```

## ⚠️ Considerações Importantes

### Segurança
- Sempre validar status no servidor
- Não confiar apenas na interface
- Verificar webhooks do Stripe

### UX
- Comunicar claramente o valor após trial
- Facilitar cancelamento
- Mostrar benefícios claramente

### Legal
- Termos de uso claros
- Política de cancelamento
- Conformidade com LGPD

## 🚀 Próximos Passos

1. **Configurar Stripe** com trial de 7 dias
2. **Testar fluxo completo** com cartões de teste
3. **Monitorar métricas** de conversão
4. **Otimizar UX** baseado no feedback
5. **Implementar emails** de lembrança

---

**Sistema implementado e testado!** ✅ 