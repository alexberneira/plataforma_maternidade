# Plataforma de Maternidade

Uma plataforma web para gestantes com informações sobre pré-natal, amamentação, nutrição e exercícios durante a gravidez.

## Requisitos

- Docker
- Docker Compose
- Git

## Instalação

1. Clone o repositório:
```bash
git clone [URL_DO_REPOSITÓRIO]
cd plataforma_maternidade
```

2. Inicie os containers Docker:
```bash
docker-compose up -d
```

3. Importe o banco de dados:
```bash
docker exec -i plataforma_maternidade_db mysql -uroot -proot < database.sql
```

4. Acesse a aplicação em http://localhost:8080

## Estrutura do Projeto

- `index.php` - Página principal com os guias e informações
- `login.php` - Página de login
- `init.php` - Configurações iniciais e conexão com o banco de dados
- `database.sql` - Estrutura e dados iniciais do banco de dados
- `pdfs/` - Diretório com os arquivos PDF dos guias

## Usuários de Teste

- Email: teste@exemplo.com
- Email: alexberneira@gmail.com

## Tecnologias Utilizadas

- PHP 8.0
- MySQL 8.0
- Bootstrap 5
- Docker
- Docker Compose

## Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

## Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes. 