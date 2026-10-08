---
type: "query"
date: "2026-10-08T20:26:46.153155+00:00"
question: "use graphify para ler o projeto e continue a refatoração"
contributor: "graphify"
outcome: "useful"
source_nodes: ["index.php", "games table", "gerenciar_games.php", "cadastrar_game.php", "conexao.php", "deletar_game.php"]
---

# Q: use graphify para ler o projeto e continue a refatoração

## Answer

Expanded from original query via vocab: [index, game, games, cadastrar, editar, deletar, gerenciar, conexao, table, seed, usuario, usuarios]. O grafo confirmou a vitrine em index.php, o CRUD em admin/views e admin/controllers, a conexão central em admin/config/conexao.php e os seeds em banco de dados/schema.txt. A refatoração preservou rotas, autenticação e campos, trocando a vitrine, o catálogo, as imagens e os tokens visuais.

## Outcome

- Signal: useful

## Source Nodes

- index.php
- games table
- gerenciar_games.php
- cadastrar_game.php
- conexao.php
- deletar_game.php