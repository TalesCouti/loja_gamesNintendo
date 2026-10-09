---
type: "query"
date: "2026-10-09T12:47:51.228082+00:00"
question: "quero que o painel de modificar, deletar e criar jogos só estejam disponíveis quando o login é feito com admin, quero tambem que o admin tenha a opção de cadastrar novos usuarios e escolher o tipo de usuario, uma nova visão dos jogos para o usuário mais como cards que são encontrados normalmente em lojas de jogos em que o usuário pode clicar para ter uma vista completa do jogo e clicar no botão de comprar, quando comprado o estoque deve ser reduzido"
contributor: "graphify"
outcome: "useful"
source_nodes: ["login.php", "index.php", "usuarios table", "games table", "cadastrar_game.php", "deletar_game.php"]
---

# Q: quero que o painel de modificar, deletar e criar jogos só estejam disponíveis quando o login é feito com admin, quero tambem que o admin tenha a opção de cadastrar novos usuarios e escolher o tipo de usuario, uma nova visão dos jogos para o usuário mais como cards que são encontrados normalmente em lojas de jogos em que o usuário pode clicar para ter uma vista completa do jogo e clicar no botão de comprar, quando comprado o estoque deve ser reduzido

## Answer

Expanded from original query via graph vocabulary: [login, user, usuario, usuarios, cadastrar, editar, deletar, gerenciar, game, games, index, table]. The graph mapped login.php, index.php, the usuarios and games tables, and the administrative CRUD pages. The implementation now authenticates both roles, synchronizes authorization from the database, restricts all administrative routes to admin, adds user management with role selection, presents uniform clickable store cards, adds a full game detail page, and performs an atomic stock decrement on CSRF-protected purchase requests.

## Outcome

- Signal: useful

## Source Nodes

- login.php
- index.php
- usuarios table
- games table
- cadastrar_game.php
- deletar_game.php