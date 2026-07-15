# Especificação do Projeto
# Cantinho das Receitas

## Objetivo

Desenvolver um portal de receitas culinárias moderno, intuitivo e responsivo, permitindo que usuários encontrem receitas, salvem favoritas, compartilhem conteúdo e publiquem suas próprias receitas.

O sistema deverá possuir foco em experiência do usuário, SEO, desempenho e facilidade de manutenção.

---

# Funcionalidades

## Página Inicial

A página inicial deverá conter:

- Barra de pesquisa
- Últimas receitas cadastradas
- Receitas mais acessadas
- Receitas em destaque
- Receitas por categoria
- Receitas aleatórias
- Receitas recomendadas
- Banner (opcional)
- Campo para login/cadastro

---

# Pesquisa

O usuário poderá pesquisar receitas por:

- Nome
- Ingredientes
- Categoria
- Tempo de preparo
- Nível de dificuldade
- Tipo de refeição

Exemplo:

Pesquisar:

```
bolo chocolate
```

Resultado:

- Bolo de Chocolate Simples
- Bolo Vulcão
- Brownie

---

# Listagem de Receitas

A tela de receitas deverá permitir filtros por:

- Categoria
- Tempo de preparo
- Dificuldade
- Custo
- Avaliação
- Ordem alfabética
- Mais recentes
- Mais populares

Cada card deverá possuir:

- Foto
- Nome
- Tempo
- Dificuldade
- Nota média
- Quantidade de avaliações

---

# Página da Receita

Cada receita deverá possuir:

## Informações básicas

- Título
- Descrição
- Foto principal
- Galeria de imagens (opcional)

---

## Informações culinárias

- Tempo de preparo
- Tempo de cozimento
- Tempo total
- Quantidade de porções
- Custo estimado

Classificação:

- Baixo
- Médio
- Alto

Nível de dificuldade:

- Fácil
- Médio
- Difícil

---

## Ingredientes

Lista completa.

Exemplo

- 2 ovos
- 500 g farinha
- 200 ml leite

---

## Modo de preparo

Passo a passo numerado.

Exemplo

1. Misture...
2. Acrescente...
3. Asse...

---

## Informações extras

- Rendimento
- Dicas
- Variações
- Observações
- Informações nutricionais (opcional)

---

## Vídeo

Campo opcional para vídeo do YouTube.

---

## Avaliações

Usuários poderão:

- dar nota de 1 a 5 estrelas
- escrever comentário
- editar comentário
- excluir comentário próprio

Será exibido:

- nota média
- quantidade de avaliações

---

## Curtidas

Botão:

❤️ Gostei

Cada usuário poderá curtir apenas uma vez.

---

## Favoritos

Botão:

Salvar receita

As receitas serão adicionadas à lista de favoritos do usuário.

---

## Compartilhamento

Compartilhar para:

- WhatsApp
- Facebook
- Telegram
- X
- Pinterest
- Copiar link

---

## Calculadora de Porções

O usuário poderá alterar o número de porções.

Exemplo

Receita original:

4 porções

Usuário seleciona:

8 porções

Todos os ingredientes serão recalculados automaticamente.

---

## Receitas relacionadas

Ao final da receita serão exibidas:

- mesma categoria
- mesmos ingredientes
- receitas populares

---

## Visualizações

Registrar:

- total de visualizações
- últimas visualizações (admin)

---

# Usuário

## Cadastro

Campos

- Nome
- Email
- Senha

Login social (opcional)

- Google

---

## Perfil

O usuário poderá editar

- foto
- nome
- biografia
- senha

---

## Área do usuário

Menu:

- Meu perfil
- Minhas receitas
- Favoritos
- Meus comentários
- Configurações

---

# Minhas Receitas

O usuário poderá

Criar

Editar

Excluir

Publicar

Salvar como rascunho

---

# Gestão de Comentários

O usuário poderá visualizar:

- comentários feitos
- respostas recebidas

---

# Categorias

Exemplo

- Bolos
- Doces
- Massas
- Carnes
- Saladas
- Bebidas
- Sobremesas
- Fitness
- Veganas
- Café da manhã

---

# Administração

O painel administrativo deverá permitir:

## Dashboard

- usuários cadastrados
- receitas cadastradas
- comentários
- avaliações
- acessos
- receitas populares

---

## Receitas

CRUD completo

---

## Categorias

CRUD completo

---

## Usuários

Gerenciar

- bloquear
- remover
- promover administrador

---

## Comentários

Moderação

Excluir comentários ofensivos.

---

## Estatísticas

Gráficos de

- acessos
- curtidas
- favoritos
- avaliações
- categorias mais acessadas

---

# SEO

Cada receita deverá possuir:

- URL amigável
- Meta Title
- Meta Description
- Open Graph
- JSON-LD Recipe
- Sitemap
- Robots

---

# Responsividade

Compatível com:

- Desktop
- Tablet
- Celular

---

# Performance

- Lazy Loading
- Compressão de imagens
- Cache
- CDN (opcional)

---

# Segurança

- Autenticação
- Criptografia de senha
- Proteção CSRF
- Proteção XSS
- Rate Limit
- Validação de uploads

---

# Banco de Dados

Principais entidades

Usuários

Categorias

Receitas

Ingredientes

ReceitaIngredientes

Comentários

Avaliações

Favoritos

Curtidas

Visualizações

Compartilhamentos

---

# Fluxo Principal

Visitante

↓

Página Inicial

↓

Pesquisa

↓

Lista de receitas

↓

Detalhe da receita

↓

Curtir

↓

Favoritar

↓

Avaliar

↓

Compartilhar

↓

Receitas relacionadas

---

# Fluxo do Usuário

Cadastro

↓

Login

↓

Perfil

↓

Criar receita

↓

Editar

↓

Publicar

↓

Gerenciar comentários

---

# Funcionalidades Futuras

- Receitas em vídeo
- Ranking de cozinheiros
- Receitas Premium
- Lista de compras
- Planejamento semanal
- Modo escuro
- IA para sugerir receitas pelos ingredientes disponíveis
- IA para converter medidas culinárias
- Impressão da receita em PDF
- Receitas por sazonalidade
- API pública