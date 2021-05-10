CREATE TABLE card
(
    uuid TEXT PRIMARY KEY NOT NULL,
    allNames JSONB,
    artist TEXT,
    availability JSONB,
    borderColor TEXT,
    cardKingdomFoilId TEXT,
    cardKingdomId TEXT,
    colorIdentity TEXT,
    colorIndicator TEXT,
    colors TEXT,
    convertedManaCost NUMERIC(10,2),
    duelDeck TEXT,
    edhrecRank INTEGER,
    faceConvertedManaCost NUMERIC(10,2),
    faceName TEXT,
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
    format_key VARCHAR(100)
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
    FOREIGN KEY(deck_id) REFERENCES deck_card(id)
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

INSERT INTO format (id,name, format_key) VALUES (1, 'Standard',  'standard');
INSERT INTO format (id,name, format_key) VALUES (2, 'Commander', 'commander');
INSERT INTO format (id,name, format_key) VALUES (3, 'Legacy',    'legacy');
INSERT INTO format (id,name, format_key) VALUES (4, 'Modern',    'modern');
INSERT INTO format (id,name, format_key) VALUES (5, 'Pauper',    'pauper');
INSERT INTO format (id,name, format_key) VALUES (6, 'Pioneer',   'pioneer');
INSERT INTO format (id,name, format_key) VALUES (7, 'Vintage',   'vintage');
INSERT INTO format (id,name, format_key) VALUES (8, 'Oldschool', 'oldschool');
INSERT INTO format (id,name, format_key) VALUES (9, 'Duel',      'duel');
INSERT INTO format (id,name, format_key) VALUES (10,'Historic',  'historic');
INSERT INTO format (id,name, format_key) VALUES (11,'Brawl',     'brawl');
INSERT INTO format (id,name, format_key) VALUES (12,'Gladiator', 'gladiator');