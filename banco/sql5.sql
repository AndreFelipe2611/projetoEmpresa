ALTER TABLE itens
    ADD categoria_id INT,
    DROP COLUMN categoria;

ALTER TABLE itens
    ADD CONSTRAINT fk_categoria
    FOREIGN KEY (categoria_id)
    REFERENCES categorias(id)
    ON DELETE CASCADE;

