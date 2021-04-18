CREATE TABLE format
(
    id INTEGER PRIMARY KEY NOT NULL,
    name varchar(100),
    format_key varchar(100)
);

CREATE TABLE deck
(
    id INTEGER PRIMARY KEY NOT NULL,
    system_user_id int not null,
    format_id int not null,
    name varchar(100),
    description varchar(500),
    colors varchar(45),
    is_valid boolean,
    creation_date date,
    FOREIGN KEY(system_user_id) REFERENCES system_user(id),
    FOREIGN KEY(format_id) REFERENCES format(id)
);

CREATE TABLE deck_card
(
    id INTEGER PRIMARY KEY NOT NULL,
    deck_id int not null,
    card_uuid varchar(36) not null,
    quantity int,
    FOREIGN KEY(deck_id) REFERENCES deck_card(id)
);

CREATE TABLE owned_card
(
    id INTEGER PRIMARY KEY NOT NULL,
    system_user_id int not null,
    card_uuid varchar(36) not null,
    quantity int,
    quantity_foil int,
    FOREIGN KEY(system_user_id) REFERENCES system_user(id)
);

CREATE TABLE card_price
(
    id INTEGER PRIMARY KEY NOT NULL,
    card_uuid varchar(36) not null,
    prices json
);