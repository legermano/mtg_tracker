CREATE TABLE card
(
    uuid TEXT PRIMARY KEY NOT NULL,
    allNames JSONB,
    artist TEXT,
    availability JSONB,
    borderColor TEXT,
    cardKingdomFoilId TEXT,
    cardKingdomId TEXT,
    colorIdentity TEXT[],
    colorIndicator TEXT,
    colors TEXT,
    convertedManaCost NUMERIC(10,2),
    duelDeck TEXT,
    edhrecRank INTEGER,
    faceConvertedManaCost NUMERIC(10,2),
    faceName TEXT,
    faceNamePTBR TEXT,
    flavorName TEXT,
    flavorText TEXT,
    flavorTextPTBR TEXT,
    frameEffects TEXT,
    frameVersion TEXT,
    hand TEXT,
    hasAlternativeDeckLimit BOOLEAN,
    hasContentWarning BOOLEAN,
    hasFoil BOOLEAN,
    hasNonFoil BOOLEAN,
    isAlternative BOOLEAN,
    isFullArt BOOLEAN,
    isOnlineOnly BOOLEAN,
    isOversized BOOLEAN,
    isPromo BOOLEAN,
    isReprint BOOLEAN,
    isReserved BOOLEAN,
    isStarter BOOLEAN,
    isStorySpotlight BOOLEAN,
    isTextless BOOLEAN,
    isTimeshifted BOOLEAN,
    keywords JSONB,
    layout TEXT,
    leadershipSkills TEXT,
    legalities JSONB,
    life TEXT,
    loyalty TEXT,
    manaCost TEXT,
    mcmId TEXT,
    mcmMetaId TEXT,
    mtgArenaId TEXT,
    mtgjsonV4Id TEXT,
    mtgoFoilId TEXT,
    mtgoId TEXT,
    multiverseId TEXT,
    multiverseIdPTBR TEXT,
    name TEXT,
    namePTBR TEXT,
    number TEXT,
    originalName TEXT,
    originalReleaseDate TEXT,
    originalText TEXT,
    originalType TEXT,
    otherFaceIds TEXT,
    power TEXT,
    printings JSONB,
    prices JSONB,
    promoTypes TEXT,
    purchaseUrls JSONB,
    rarity TEXT,
    rullings JSONB,
    scryfallId TEXT,
    scryfallIllustrationId TEXT,
    scryfallOracleId TEXT,
    setCode TEXT,
    setName TEXT,
    side TEXT,
    subtypes JSONB,
    supertypes JSONB,
    tcgplayerProductId TEXT,
    text TEXT,
    textPTBR TEXT,
    toughness TEXT,
    type TEXT,
    typePTBR TEXT,
    types JSONB,
    variations JSONB,
    watermark TEXT
);
CREATE INDEX idxallNames ON card USING GIN((allNames -> 'names'));
CREATE INDEX idxavailability ON card USING GIN((availability -> 'availability'));
CREATE INDEX idxkeywords ON card USING GIN((keywords -> 'keywords'));
CREATE INDEX idxlegalities ON card USING GIN((legalities -> 'legalities'));
CREATE INDEX idxprintings ON card USING GIN((printings -> 'printings'));
CREATE INDEX idxpurchaseUrls ON card USING GIN((purchaseUrls -> 'purchaseUrls'));
CREATE INDEX idxrullings ON card USING GIN((rullings -> 'rullings'));
CREATE INDEX idxsubtypes ON card USING GIN((subtypes -> 'subtypes'));
CREATE INDEX idxsupertypes ON card USING GIN((supertypes -> 'supertypes'));
CREATE INDEX idxtypes ON card USING GIN((types -> 'types'));
CREATE INDEX idxvariations ON card USING GIN((variations -> 'variations'));

CREATE TABLE format
(
    id INTEGER PRIMARY KEY NOT NULL,
    name VARCHAR(100),
    format_key VARCHAR(100),
    min_size INTEGER,
    max_size INTEGER,
    sideboard INTEGER
);

CREATE TABLE deck
(
    id INTEGER PRIMARY KEY NOT NULL,
    system_user_id int not null,
    format_id int not null,
    name VARCHAR(100),
    description VARCHAR(500),
    colors VARCHAR(45),
    is_valid boolean,
    creation_date date,
    FOREIGN KEY(system_user_id) REFERENCES system_user(id),
    FOREIGN KEY(format_id) REFERENCES format(id)
);

CREATE TABLE deck_card
(
    id INTEGER PRIMARY KEY NOT NULL,
    deck_id int not null,
    card_uuid TEXT not null,
    quantity int,
    FOREIGN KEY(deck_id) REFERENCES deck(id) ON DELETE CASCADE
);

CREATE TABLE owned_card
(
    id INTEGER PRIMARY KEY NOT NULL,
    system_user_id int not null,
    card_uuid TEXT not null,
    quantity int,
    quantity_foil int,
    FOREIGN KEY(system_user_id) REFERENCES system_user(id)
);

CREATE TABLE set
(
    code TEXT PRIMARY KEY NOT NULL,
    baseSetSize INTEGER,
    block TEXT,
    isFoilOnly BOOLEAN,
    isForeignOnly BOOLEAN,
    isNonFoilOnly BOOLEAN,
    isOnlineOnly BOOLEAN,
    isPartialPreview BOOLEAN,
    keyruneCode TEXT,
    mcmId INTEGER,
    mcmIdExtras INTEGER,
    mcmName TEXT,
    mtgoCode TEXT,
    name TEXT,
    parentCode TEXT,
    releaseDate DATE,
    tcgplayerGroupId INTEGER,
    totalSetSize INTEGER,
    type TEXT
);

INSERT INTO format (id,name, format_key, min_size, max_size, sideboard) VALUES (1, 'Standard',  'standard',  60,   NULL, 15);
INSERT INTO format (id,name, format_key, min_size, max_size, sideboard) VALUES (2, 'Commander', 'commander', 100,  100,  NULL);
INSERT INTO format (id,name, format_key, min_size, max_size, sideboard) VALUES (3, 'Legacy',    'legacy',    60,   NULL, 15);
INSERT INTO format (id,name, format_key, min_size, max_size, sideboard) VALUES (4, 'Modern',    'modern',    60,   NULL, 15);
INSERT INTO format (id,name, format_key, min_size, max_size, sideboard) VALUES (5, 'Pauper',    'pauper',    NULL, NULL, NULL);
INSERT INTO format (id,name, format_key, min_size, max_size, sideboard) VALUES (6, 'Pioneer',   'pioneer',   60,   NULL, 15);
INSERT INTO format (id,name, format_key, min_size, max_size, sideboard) VALUES (7, 'Vintage',   'vintage',   60,   NULL, 15);
INSERT INTO format (id,name, format_key, min_size, max_size, sideboard) VALUES (8, 'Oldschool', 'oldschool', NULL, NULL, NULL);
INSERT INTO format (id,name, format_key, min_size, max_size, sideboard) VALUES (9, 'Duel',      'duel',      100,  NULL, NULL);

-------------------------------VIEWS--------------------------------------------
-- Owned cards ptbr
CREATE OR REPLACE FUNCTION owned_card_view (user_id INTEGER, card TEXT, sets TEXT, lang TEXT)
    RETURNS TABLE (set_name TEXT,card_name TEXT, side TEXT, quantity INTEGER, quantity_foil INTEGER)
    LANGUAGE plpgsql AS
$func$
BEGIN
    IF $4 = 'PTBR' THEN
        IF $3 = '' THEN
            RETURN QUERY
            SELECT set.name AS set_name , COALESCE(namePTBR,COALESCE(faceName,card.name)) AS card_name,card.side, owned_card.quantity, owned_card.quantity_foil
            FROM owned_card
            INNER JOIN card ON (card_uuid = uuid)
            INNER JOIN set  ON (setcode   = code)
            WHERE system_user_id = $1
            AND (owned_card.quantity > 0 OR owned_card.quantity_foil > 0)
            AND (allnames ->> 'names' ilike $2 OR coalesce(facename,card.name) ilike $2)
            ORDER BY set.name ASC, card_name;
        ELSE
            RETURN QUERY
            SELECT set.name AS set_name , COALESCE(namePTBR,COALESCE(faceName,card.name)) AS card_name,card.side, owned_card.quantity, owned_card.quantity_foil
            FROM owned_card
            INNER JOIN card ON (card_uuid = uuid)
            INNER JOIN set  ON (setcode   = code)
            WHERE system_user_id = $1
            AND (owned_card.quantity > 0 OR owned_card.quantity_foil > 0)
            AND (allnames ->> 'names' ilike $2 OR coalesce(facename,card.name) ilike $2)
            AND code = ANY(STRING_TO_ARRAY($3,','))
            ORDER BY set.name ASC, card_name;
        END IF;
    ELSE
        IF $3 = '' THEN
            RETURN QUERY
            SELECT set.name AS set_name , COALESCE(faceName,card.name) AS card_name,card.side, owned_card.quantity, owned_card.quantity_foil
            FROM owned_card
            INNER JOIN card ON (card_uuid = uuid)
            INNER JOIN set  ON (setcode   = code)
            WHERE system_user_id = $1
            AND (owned_card.quantity > 0 OR owned_card.quantity_foil > 0)
            AND (allnames ->> 'names' ilike $2 OR coalesce(facename,card.name) ilike $2)
            ORDER BY set.name ASC, card_name;
        ELSE
            RETURN QUERY
            SELECT set.name AS set_name , COALESCE(faceName,card.name) AS card_name,card.side, owned_card.quantity, owned_card.quantity_foil
            FROM owned_card
            INNER JOIN card ON (card_uuid = uuid)
            INNER JOIN set  ON (setcode   = code)
            WHERE system_user_id = $1
            AND (owned_card.quantity > 0 OR owned_card.quantity_foil > 0)
            AND (allnames ->> 'names' ilike $2 OR coalesce(facename,card.name) ilike $2)
            AND code = ANY(STRING_TO_ARRAY($3,','))
            ORDER BY set.name ASC, card_name;
        END IF;
    END IF;
END
$func$;
-----------------------------DECK MOCK------------------------------------------------------------------------
INSERT INTO deck VALUES (((SELECT COALESCE(MAX(id),0) FROM deck) + 1),4,1,'Mono black Set Core 21','Mono black Set Core 21','B',TRUE,'2021-05-18');
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), 'd284ed49-e301-5683-9385-bbe8a69bee80',2);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), 'c28e5e66-5cde-5236-b22d-c98174759403',3);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), 'b14d7137-24df-5eb3-b127-22243e197cc5',4);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), 'bde3d656-05f1-51b5-b219-a25902e5c5cf',3);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), 'a5837977-dd65-5900-beed-b2159f4a5b68',3);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), '4e46dffa-66fe-58ae-9028-80c77d53c94b',2);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), 'bdf689f5-3561-57e7-abdc-6cdc07091814',2);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), 'da8244ce-fde3-5398-9639-4fa7648ae15e',1);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), 'f7056037-3a6b-5fba-918d-9f78d58bfd89',2);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), '3365d3cb-d481-5d77-aa47-910cc85392ec',1);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), '1d9232a3-d088-5d79-8eba-97aaf4fc66a9',3);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), 'f58458a9-e3ff-53cf-9758-649e1cfce1f4',3);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), '697eedcb-fc2f-5232-a3ba-39973ab4be7e',2);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), '8517e5f6-ba72-5fe6-aa43-b3184c6ffe6b',1);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), 'a0180d8b-6cfb-5c6d-8951-c00497010b9b',1);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), '9021fd6e-48d7-5c3d-858e-de0dd6bf5b29',9);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), 'e0a00c3e-6d17-5e85-9d1d-c704d828a576',9);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'Mono black Set Core 21'), '9ebb924e-395f-5e14-8be5-176d349d1dd9',8);

INSERT INTO deck VALUES (((SELECT COALESCE(MAX(id),0) FROM deck) + 1),4,1,'White Weenie','Mono white White Weenie 2021 KHM','W',TRUE,'2021-05-18');
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'White Weenie'), 'aa8816f1-8810-5132-af0e-96fbd1e08888',4);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'White Weenie'), '91680403-7ed7-5c5a-8f38-b2005200e27a',2);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'White Weenie'), '27e7feb4-9849-5908-9632-82075e26ede7',4);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'White Weenie'), 'cee1fbfb-f780-50ef-8fa4-e1db9798b21d',2);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'White Weenie'), 'a2f896c9-857f-5ad8-aa4e-06fe8af109ae',4);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'White Weenie'), '3bff6225-e956-5bee-b549-bb5e5fa22386',2);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'White Weenie'), 'c05a79d5-f08f-5a11-a2d9-5b058b81f492',4);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'White Weenie'), '52a97e8b-5446-56c9-bb11-c73311f8af09',4);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'White Weenie'), 'a3a20ff0-f502-5bb4-8f5a-bc9b03bbfca7',4);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'White Weenie'), 'f0c356e8-740c-5363-9fde-56f4902492ae',2);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'White Weenie'), 'ef368548-311c-53fd-9941-f1ad13636f90',4);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'White Weenie'), '4d175cbb-33c1-5e06-a9a5-bfd90cac55ee',2);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'White Weenie'), 'd23b96b7-fc82-59dc-a7e4-35d79e06e487',4);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'White Weenie'), '278e5356-5ac2-5727-94e5-99e3c984b120',2);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'White Weenie'), '8561bc1c-5170-540d-be31-05b69bbdb807',14);
INSERT INTO deck_card VALUES (((SELECT COALESCE(MAX(id),0) FROM deck_card) + 1), (SELECT MIN(id) FROM deck WHERE name = 'White Weenie'), '5b0de032-d690-5556-b888-7c4a514e73bf',4);