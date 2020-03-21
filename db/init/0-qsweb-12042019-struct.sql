--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.12
-- Dumped by pg_dump version 11.2

-- Started on 2019-04-12 11:14:30

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 1 (class 3079 OID 16394)
-- Name: adminpack; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS adminpack WITH SCHEMA pg_catalog;


--
-- TOC entry 2499 (class 0 OID 0)
-- Dependencies: 1
-- Name: EXTENSION adminpack; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION adminpack IS 'administrative functions for PostgreSQL';


--
-- TOC entry 4 (class 3079 OID 16403)
-- Name: pg_trgm; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pg_trgm WITH SCHEMA public;


--
-- TOC entry 2500 (class 0 OID 0)
-- Dependencies: 4
-- Name: EXTENSION pg_trgm; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION pg_trgm IS 'text similarity measurement and index searching based on trigrams';


--
-- TOC entry 3 (class 3079 OID 16468)
-- Name: unaccent; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS unaccent WITH SCHEMA public;


--
-- TOC entry 2501 (class 0 OID 0)
-- Dependencies: 3
-- Name: EXTENSION unaccent; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION unaccent IS 'text search dictionary that removes accents';


--
-- TOC entry 265 (class 1255 OID 16475)
-- Name: blower(character varying); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.blower(dt character varying) RETURNS character varying
    LANGUAGE plpgsql IMMUTABLE
    AS $$
    BEGIN
        RETURN unaccent(UPPER(dt));
    END;
$$;


--
-- TOC entry 266 (class 1255 OID 16476)
-- Name: createnodetree(integer, integer, integer, text); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.createnodetree(nodeidparent integer, idname integer, idnameori integer, descr text) RETURNS integer
    LANGUAGE plpgsql
    AS $$

DECLARE
    v_gauche Integer;
    v_droite Integer;
    newGauche Integer;
    newDroite Integer;
    v_id Integer;
    
BEGIN
    SELECT gauche, droite INTO v_gauche, v_droite from cle WHERE id_cle = nodeIdParent;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Clé (%) parente est inexistante ! ', nodeIdParent;
    END IF;

          newGauche = v_droite;
          newDroite = v_droite + 1;
          UPDATE cle SET droite = droite + 2 WHERE droite >= v_droite;
          UPDATE cle SET gauche = gauche + 2 WHERE gauche > v_droite;

          INSERT INTO cle (idparent, fk_id_name, id_name_ori, descr, gauche, droite) VALUES (nodeIdParent, idName, idNameOri, descr, newGauche, newDroite) RETURNING id_cle INTO v_id;
          
    return v_id;
END;

$$;


--
-- TOC entry 267 (class 1255 OID 16477)
-- Name: deletenodetree(integer); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.deletenodetree(idcle integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$

DECLARE
    v_gauche Integer;
    v_droite Integer;
    
BEGIN
    SELECT gauche, droite INTO v_gauche, v_droite from cle WHERE id_cle = idcle;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Clé (%) est inexistante ! ', idcle;
    END IF;
    RAISE NOTICE 'DEL clé % with : gauche (%), droite (%)', idcle, v_gauche, v_droite;
    DELETE FROM cle WHERE gauche BETWEEN v_gauche AND v_droite;
    UPDATE cle SET droite = droite - (v_droite - v_gauche + 1) WHERE droite > v_droite;
    UPDATE cle SET gauche = gauche - (v_droite - v_gauche + 1) WHERE gauche > v_droite;
    return 1;
END;

$$;


--
-- TOC entry 268 (class 1255 OID 16478)
-- Name: distance_between_points(double precision, double precision, double precision, double precision); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.distance_between_points(lo_target double precision, la_target double precision, lo_ref double precision, la_ref double precision) RETURNS integer
    LANGUAGE plpgsql
    AS $$

BEGIN
 	return (6366*acos(cos(radians(la_ref))*cos(radians(la_target))*cos(radians(lo_target)-radians(lo_ref))+sin(radians(la_ref))*sin(radians(la_target))));
END;
$$;


--
-- TOC entry 269 (class 1255 OID 16479)
-- Name: movenodetree(integer, integer); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.movenodetree(nodeid integer, nodeparent integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$

DECLARE
    v_gauche Integer;
    v_gauche_parent Integer;
    v_droite Integer;
    v_droite_parent Integer;
    v_new_droite Integer;
    deltaGD Integer;
    
BEGIN
    
    SELECT gauche, droite INTO v_gauche, v_droite from cle WHERE id_cle = nodeId;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Clé (%) est inexistante ! ', nodeId;
    END IF;
    SELECT gauche, droite INTO v_gauche_parent, v_droite_parent FROM cle WHERE id_cle = nodeParent;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Clé (%) parente est inexistante ! ', nodeParent;
    END IF;
    
    -- check if the new parent is a children of node that move
    IF v_gauche_parent > v_gauche AND v_droite_parent < v_droite THEN
        RAISE EXCEPTION 'Vous ne pouvez pas déplacer le parent d''un noeud dans celui-ci !';
    END IF;

    -- change parent for node
    UPDATE cle SET idparent = nodeParent WHERE id_cle = nodeId;

    -- init gauche, droite to 0 for node
    UPDATE cle SET gauche = 0, droite = 0 
    WHERE gauche >= v_gauche
    AND gauche < v_droite; 
    
    deltaGD := v_droite - v_gauche + 1;

    RAISE NOTICE 'DELTA Gauche et droite % : gauche (%), droite (%)', deltaGD, v_gauche, v_droite;

    -- decrement counter gauche, droite
    UPDATE cle SET droite = droite - deltaGD WHERE droite > v_droite;
    UPDATE cle SET gauche = gauche - deltaGD WHERE gauche > v_droite;

    SELECT gauche, droite INTO v_gauche_parent, v_droite_parent from cle WHERE id_cle = nodeParent;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Clé (%) parente est inexistante ! ', nodeParent;
    END IF;
    
    
    
    -- Get new droite from node
    SELECT droite INTO v_new_droite from cle WHERE id_cle = nodeParent;
    
    -- increment counter gauche, droite
    UPDATE cle SET droite = droite + deltaGD WHERE droite >= v_new_droite;
    UPDATE cle SET gauche = gauche + deltaGD WHERE gauche > v_new_droite;
    -- update droite with max + 1 of children
    
    -- renum node with new gauche, droite
    PERFORM treeGenerator(nodeId, v_droite_parent);

    -- check if gauche or droite are renumber
    PERFORM descr, id_cle, idparent, gauche, droite from cle where gauche = 0 OR droite = 0;
    IF FOUND THEN
        RAISE EXCEPTION 'Un problème est survenu lors de la renumérotation des champs gauche, droite ! ';
    END IF;

    return 1;
END;

$$;


--
-- TOC entry 270 (class 1255 OID 16480)
-- Name: restorebackupnom(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.restorebackupnom() RETURNS integer
    LANGUAGE plpgsql
    AS $$

DECLARE
    rowNom RECORD;
    rowTempNom RECORD;
    rowTaxon RECORD;
    idsAdd Integer[];
    diffAccepted Integer;
    queryMaj varchar := '';
BEGIN

    /* DELETE */
    FOR rowNom IN SELECT * FROM nom WHERE id_name NOT IN (SELECT id_name FROM temp_nom) LOOP
        -- disable taxon
        IF rowNom.statut_syn = 'A' OR rowNom.statut_syn = 'A*' THEN
            UPDATE taxon SET active = '0' WHERE id_name = rowNom.id_name;
            RAISE NOTICE 'DELETE : Le nom (%) est un taxon, mise a jour de taxon (active = 0)', rowNom.id_name;
        END IF;
    END LOOP;

    /* ADD */
    FOR rowTempNom IN SELECT * FROM temp_nom WHERE id_name NOT IN (SELECT id_name FROM nom) LOOP
        
        INSERT INTO nom SELECT * FROM temp_nom WHERE id_name = rowTempNom.id_name;
        RAISE NOTICE 'ADD : Le nom (%) a été ajouté', rowTempNom.id_name;

        idsAdd := array_append(idsAdd, rowTempNom.id_name);

        IF rowTempNom.statut_syn = 'A' OR rowTempNom.statut_syn = 'A*' THEN
            -- check if they are any name not A
            PERFORM id_name FROM temp_nom WHERE fk_id_a = rowTempNom.id_name AND id_name <> fk_id_a AND statut_syn <> 'A' AND statut_syn <> 'A*';
            IF NOT FOUND THEN
                INSERT INTO taxon (id_name, nom_standard) VALUES (rowTempNom.id_name, rowTempNom.nom_standard);
                RAISE NOTICE 'ADD : Le taxon (%) a été ajouté', rowTempNom.id_name;
            END IF;
        END IF;
        
    END LOOP;

    /* UPDATE */
    queryMaj := 'SELECT tn.* FROM temp_nom tn, nom n
                 WHERE tn.id_name = n.id_name
                 AND (tn.fk_id_parent <> n.fk_id_parent OR
                     tn.fk_id_a <> n.fk_id_a OR
                     tn.fk_famille <> n.fk_famille OR
                     tn.genre <> n.genre OR
                     tn.no_rang <> n.no_rang OR
                     tn.nom_standard <> n.nom_standard OR
                     tn.statut_syn <> n.statut_syn OR
                     tn.fk_id_genre <> n.fk_id_genre
                    )';

    /* EXCLUDE ADDED ROWS */
    IF array_length(idsAdd, 1) > 0 THEN
        queryMaj := queryMaj || format(' AND tn.id_name <> ANY(%L)', idsAdd);
    END IF;

    /* SELECT ALL WITHOUT ADDED ROWS */
    FOR rowTempNom IN EXECUTE queryMaj LOOP
        
        UPDATE nom SET  fk_id_parent    = rowTempNom.fk_id_parent,
                        fk_id_a         = rowTempNom.fk_id_a,
                        fk_famille      = rowTempNom.fk_famille,
                        genre           = rowTempNom.genre,
                        no_rang         = rowTempNom.no_rang,
                        nom_standard    = rowTempNom.nom_standard,
                        statut_syn      = rowTempNom.statut_syn,
                        fk_id_genre     = rowTempNom.fk_id_genre
                    WHERE id_name       = rowTempNom.id_name;
        RAISE NOTICE 'UPDATE : Le nom (%) a été modifié', rowTempNom.id_name;
    END LOOP;

    -- After update, if taxon has reference to not accepted name then use fk_id_a
    FOR rowNom IN SELECT * FROM taxon t INNER JOIN nom n ON t.id_name = n.id_name WHERE t.active = '1' AND (n.statut_syn <> 'A' OR n.statut_syn <> 'A*')  LOOP
        -- update taxon with accepted name
        UPDATE taxon SET id_name = rowNom.fk_id_a, nom_standard = rowNom.nom_standard WHERE id_taxon = rowNom.id_taxon;
        RAISE NOTICE 'UPDATE : (A -> *) Le taxon (%) a été modifié avec l''id_name (%)', rowNom.id_taxon, rowNom.fk_id_a;
    END LOOP;


    -- check if name accepted is equals as taxon actived
    SELECT ( SELECT COUNT(*) FROM nom WHERE statut_syn = 'A' OR statut_syn <> 'A*' ) - ( SELECT COUNT(*) FROM taxon) as total_rows INTO diffAccepted;
    IF diffAccepted <> 0 THEN
        RAISE EXCEPTION 'Il y a pas le même nombre de nom acceptés et de taxons (nom - taxon = %)', diffAccepted;
    END IF;
    return 1;
END;

$$;


--
-- TOC entry 271 (class 1255 OID 16481)
-- Name: treegenerator(integer, integer); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.treegenerator(node integer DEFAULT 0, currentcounter integer DEFAULT 0) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    rowNode RECORD;
    nbChilds integer;
    i integer;
    maxdroite integer;
BEGIN
    /* GAUCHE */
    UPDATE cle SET gauche = currentCounter WHERE id_cle = node;
    
    /* NOMBRE D'ENFANTS POUR LE NOEUD  */
    SELECT count(*) INTO nbChilds FROM cle WHERE idparent = node; 
    IF nbChilds = 0 THEN
        currentCounter := currentCounter + 1;
        UPDATE cle SET droite = currentCounter WHERE id_cle = node;
        RETURN currentCounter;
    END IF;

    i := 0;
    /* ENUMERATION DE CHAQUE ENFANT */
    FOR rowNode IN SELECT * FROM cle WHERE idparent = node ORDER BY idparent, id_cle LOOP
        currentCounter := currentCounter + 1;
        IF nbChilds = 1 THEN
            i := 1;
        END IF;
        currentCounter :=  i + treeGenerator(rowNode.id_cle, currentCounter);
        i := i + 1;
    END LOOP;

    /* DROITE */
    SELECT MAX(droite) into maxdroite FROM cle WHERE idparent = node;
    UPDATE cle SET droite = maxdroite + 1 WHERE id_cle = node;

    RETURN currentCounter;
END;
$$;


--
-- TOC entry 272 (class 1255 OID 16482)
-- Name: update_modified_column(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.update_modified_column() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.date_modification = now();
    RETURN NEW;
END;
$$;


SET default_with_oids = false;

--
-- TOC entry 188 (class 1259 OID 16483)
-- Name: biblio; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.biblio (
    no_bib integer NOT NULL,
    date_creation timestamp(3) with time zone DEFAULT now(),
    date_modification timestamp(3) with time zone DEFAULT now(),
    fk_idname integer,
    abbrev_longue character varying(500),
    annee numeric,
    proto integer,
    no_ref integer
);


--
-- TOC entry 189 (class 1259 OID 16491)
-- Name: biblio_no_bib_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.biblio_no_bib_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2502 (class 0 OID 0)
-- Dependencies: 189
-- Name: biblio_no_bib_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.biblio_no_bib_seq OWNED BY public.biblio.no_bib;


--
-- TOC entry 190 (class 1259 OID 16493)
-- Name: cle_id_cle_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cle_id_cle_seq
    START WITH 8978
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 191 (class 1259 OID 16495)
-- Name: cle; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cle (
    balise character varying(255),
    famille character varying(255),
    id_genre_ordre integer,
    "genre$" character varying(255),
    idparent integer,
    id_cle integer DEFAULT nextval('public.cle_id_cle_seq'::regclass) NOT NULL,
    num character varying(255),
    descr text,
    liennum character varying(255),
    "espece$" character varying(255),
    numqs character varying(255),
    date_creation timestamp with time zone,
    date_modification timestamp with time zone,
    id_name_ori integer,
    fk_id_famille integer,
    gauche integer DEFAULT 0,
    droite integer DEFAULT 0,
    fk_id_taxon integer,
    fk_id_name integer
);


--
-- TOC entry 192 (class 1259 OID 16504)
-- Name: collecteur; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.collecteur (
    id_coll integer NOT NULL,
    nom character varying(255) DEFAULT NULL::character varying,
    activites character varying(30),
    date_creation timestamp(3) with time zone DEFAULT now() NOT NULL,
    date_modification timestamp(3) with time zone DEFAULT now() NOT NULL
);


--
-- TOC entry 193 (class 1259 OID 16510)
-- Name: collecteur_id_coll_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.collecteur_id_coll_seq
    START WITH 891
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2503 (class 0 OID 0)
-- Dependencies: 193
-- Name: collecteur_id_coll_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.collecteur_id_coll_seq OWNED BY public.collecteur.id_coll;


--
-- TOC entry 194 (class 1259 OID 16512)
-- Name: description_id_description_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.description_id_description_seq
    START WITH 5162
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 195 (class 1259 OID 16514)
-- Name: description; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.description (
    id_description integer DEFAULT nextval('public.description_id_description_seq'::regclass) NOT NULL,
    "ID_ordre" double precision,
    "BALISE" character varying(255),
    "FAMILLE" character varying(255),
    "GENRE " character varying(255),
    "ID_QS" character varying(255),
    descr text,
    distribution character varying(255),
    esp character varying(255),
    ecologie character varying(255),
    remarque character varying(255),
    date_creation timestamp(3) with time zone DEFAULT now(),
    date_modification timestamp(3) with time zone DEFAULT now(),
    fk_id_name_ori integer,
    fk_id_taxon integer
);


--
-- TOC entry 196 (class 1259 OID 16523)
-- Name: distrib_biblio; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.distrib_biblio (
    id_d integer NOT NULL,
    fk_id_loc integer,
    fk_id_name integer,
    fk_no_bib integer,
    page character varying(255) DEFAULT NULL::character varying,
    remarque character varying(255) DEFAULT NULL::character varying,
    createur character varying(255) DEFAULT NULL::character varying,
    date_creation timestamp(3) with time zone DEFAULT now() NOT NULL,
    date_modification timestamp(3) with time zone DEFAULT now() NOT NULL
);


--
-- TOC entry 197 (class 1259 OID 16534)
-- Name: distrib_biblio_id_d_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.distrib_biblio_id_d_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2504 (class 0 OID 0)
-- Dependencies: 197
-- Name: distrib_biblio_id_d_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.distrib_biblio_id_d_seq OWNED BY public.distrib_biblio.id_d;


--
-- TOC entry 198 (class 1259 OID 16536)
-- Name: nom; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.nom (
    id_name integer NOT NULL,
    fk_id_parent integer,
    fk_id_a integer,
    fk_famille character varying(255) DEFAULT NULL::character varying,
    genre character varying(50) DEFAULT NULL::character varying,
    no_rang smallint,
    nom_standard character varying(255) DEFAULT NULL::character varying,
    statut_syn character varying(2) DEFAULT NULL::character varying,
    date_creation timestamp(3) with time zone DEFAULT now(),
    date_modification timestamp(3) with time zone DEFAULT now(),
    fk_id_genre integer
);


--
-- TOC entry 199 (class 1259 OID 16548)
-- Name: espece; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.espece AS
 SELECT nom.id_name,
    nom.fk_id_a,
    nom.fk_id_parent,
    nom.fk_famille,
    nom.fk_id_genre,
    nom.no_rang,
    nom.nom_standard,
    nom.statut_syn,
    nom.date_creation,
    nom.date_modification
   FROM public.nom
  WHERE (nom.no_rang > 14);


--
-- TOC entry 200 (class 1259 OID 16552)
-- Name: famille; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.famille AS
 SELECT nom.id_name,
    nom.nom_standard,
    nom.fk_famille AS famille,
    nom.fk_id_parent,
    nom.date_creation,
    nom.date_modification
   FROM public.nom
  WHERE (nom.no_rang = 6);


--
-- TOC entry 201 (class 1259 OID 16556)
-- Name: genre; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.genre AS
 SELECT nom.id_name,
    nom.fk_id_parent AS fk_id_famille,
    nom.fk_id_a,
    nom.nom_standard,
    nom.statut_syn,
    nom.date_creation,
    nom.date_modification
   FROM public.nom
  WHERE (nom.no_rang = 9);


--
-- TOC entry 202 (class 1259 OID 16560)
-- Name: image; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.image (
    id_image integer NOT NULL,
    "ID_ordre" double precision,
    figcaption character varying(255),
    auteur character varying(255),
    id_recolte character varying(255),
    date_ajout character varying(255),
    remarque character varying(255),
    dossier character varying(255),
    image_old character varying(255),
    recolte_ncoll character varying(255),
    id_tbl_apd integer,
    date_creation timestamp(3) with time zone DEFAULT now(),
    temp character varying(255),
    image character varying(255),
    date_modification timestamp(3) with time zone DEFAULT now(),
    fk_id_recolte integer,
    fk_id_taxon integer,
    fk_id_name_ori integer
);


--
-- TOC entry 219 (class 1259 OID 17424)
-- Name: livres; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.livres (
    id_livre_apd integer NOT NULL,
    full_name character varying(255),
    titre character varying(255),
    auteur character varying(255),
    date character varying(255)
);


--
-- TOC entry 222 (class 1259 OID 17456)
-- Name: livres_id_livres_apd_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.livres_id_livres_apd_seq
    START WITH 20751
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2505 (class 0 OID 0)
-- Dependencies: 222
-- Name: livres_id_livres_apd_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.livres_id_livres_apd_seq OWNED BY public.livres.id_livre_apd;


--
-- TOC entry 203 (class 1259 OID 16568)
-- Name: localite; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.localite (
    id_loc integer NOT NULL,
    fk_pays character varying(255),
    fk_id_utilisateur smallint,
    lat_dec double precision,
    long_dec double precision,
    full_name character varying(255),
    type_loc character varying(255) DEFAULT NULL::character varying,
    province character varying(255) DEFAULT NULL::character varying,
    precision_loc integer,
    alt integer,
    date_creation timestamp(3) with time zone DEFAULT now(),
    date_modification timestamp(3) with time zone DEFAULT now()
);


--
-- TOC entry 204 (class 1259 OID 16578)
-- Name: localite_id_loc_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.localite_id_loc_seq
    START WITH 63315
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2506 (class 0 OID 0)
-- Dependencies: 204
-- Name: localite_id_loc_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.localite_id_loc_seq OWNED BY public.localite.id_loc;


--
-- TOC entry 205 (class 1259 OID 16580)
-- Name: nom_new; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.nom_new (
    id_name integer NOT NULL,
    fk_id_parent integer,
    fk_id_a integer,
    fk_famille character varying(255) DEFAULT NULL::character varying,
    genre character varying(50) DEFAULT NULL::character varying,
    no_rang smallint,
    nom_standard character varying(255) DEFAULT NULL::character varying,
    statut_syn character varying(2) DEFAULT NULL::character varying,
    date_creation timestamp(3) with time zone DEFAULT now(),
    date_modification timestamp(3) with time zone DEFAULT now(),
    fk_id_genre integer
);


--
-- TOC entry 206 (class 1259 OID 16592)
-- Name: taxon_id_taxon_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.taxon_id_taxon_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 207 (class 1259 OID 16594)
-- Name: taxon; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.taxon (
    id_taxon integer DEFAULT nextval('public.taxon_id_taxon_seq'::regclass) NOT NULL,
    id_name integer NOT NULL,
    nom_standard character varying(255) DEFAULT NULL::character varying,
    active character varying(2) DEFAULT '1'::character varying,
    uid character varying(40)
);


--
-- TOC entry 208 (class 1259 OID 16600)
-- Name: nom_v_node_explore; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.nom_v_node_explore AS
 WITH RECURSIVE tree AS (
         SELECT taxon.id_taxon,
            nom.id_name,
                CASE
                    WHEN (nom.no_rang = 6) THEN taxon.id_taxon
                    ELSE NULL::integer
                END AS idtaxonfamily,
                CASE
                    WHEN (nom.no_rang = 6) THEN (taxon.nom_standard)::text
                    ELSE NULL::text
                END AS family,
                CASE
                    WHEN (nom.no_rang = 9) THEN taxon.id_taxon
                    ELSE NULL::integer
                END AS idtaxongenus,
                CASE
                    WHEN (nom.no_rang = 9) THEN (taxon.nom_standard)::text
                    ELSE NULL::text
                END AS genus,
                CASE
                    WHEN (nom.no_rang = 15) THEN taxon.id_taxon
                    ELSE NULL::integer
                END AS idtaxonspecies,
                CASE
                    WHEN (nom.no_rang = 15) THEN (taxon.nom_standard)::text
                    ELSE NULL::text
                END AS species,
                CASE
                    WHEN (nom.no_rang > 15) THEN taxon.id_taxon
                    ELSE NULL::integer
                END AS idtaxoninfraspecies,
                CASE
                    WHEN (nom.no_rang > 15) THEN (taxon.nom_standard)::text
                    ELSE NULL::text
                END AS infraspecies
           FROM public.nom,
            public.taxon
          WHERE ((taxon.id_name = nom.id_name) AND (nom.fk_id_parent IS NULL))
        UNION ALL
         SELECT taxon.id_taxon,
            nom.id_name,
                CASE
                    WHEN ((tree_1.idtaxonfamily IS NULL) AND (nom.no_rang = 6)) THEN taxon.id_taxon
                    ELSE tree_1.idtaxonfamily
                END AS idtaxonfamily,
                CASE
                    WHEN ((tree_1.family IS NULL) AND (nom.no_rang = 6)) THEN (taxon.nom_standard)::text
                    ELSE tree_1.family
                END AS family,
                CASE
                    WHEN ((tree_1.idtaxongenus IS NULL) AND (nom.no_rang = 9)) THEN taxon.id_taxon
                    ELSE tree_1.idtaxongenus
                END AS idtaxongenus,
                CASE
                    WHEN ((tree_1.genus IS NULL) AND (nom.no_rang = 9)) THEN (taxon.nom_standard)::text
                    ELSE tree_1.genus
                END AS genus,
                CASE
                    WHEN ((tree_1.idtaxonspecies IS NULL) AND (nom.no_rang = 15)) THEN taxon.id_taxon
                    ELSE tree_1.idtaxonspecies
                END AS idtaxonspecies,
                CASE
                    WHEN ((tree_1.species IS NULL) AND (nom.no_rang = 15)) THEN (taxon.nom_standard)::text
                    ELSE tree_1.species
                END AS species,
                CASE
                    WHEN ((tree_1.idtaxoninfraspecies IS NULL) AND (nom.no_rang > 15)) THEN taxon.id_taxon
                    ELSE tree_1.idtaxoninfraspecies
                END AS idtaxoninfraspecies,
                CASE
                    WHEN ((tree_1.infraspecies IS NULL) AND (nom.no_rang > 15)) THEN (taxon.nom_standard)::text
                    ELSE tree_1.infraspecies
                END AS infraspecies
           FROM public.nom,
            public.taxon,
            tree tree_1
          WHERE ((taxon.id_name = nom.id_name) AND (nom.fk_id_parent = tree_1.id_name))
        )
 SELECT tree.id_taxon,
    tree.id_name,
    tree.idtaxonfamily,
    tree.family,
    tree.idtaxongenus,
    tree.genus,
    tree.idtaxonspecies,
    tree.species,
    tree.idtaxoninfraspecies,
    tree.infraspecies
   FROM tree;


--
-- TOC entry 220 (class 1259 OID 17438)
-- Name: obs_livre; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.obs_livre (
    fk_id_loc integer,
    fk_livre integer,
    page character varying(255) DEFAULT NULL::character varying,
    remarque character varying(255) DEFAULT NULL::character varying,
    date_creation timestamp(3) with time zone DEFAULT now() NOT NULL,
    date_modification timestamp(3) with time zone DEFAULT now() NOT NULL,
    fk_id_collecteur integer,
    date_obs time with time zone,
    fiabilie integer,
    id_obs integer NOT NULL,
    type_obs integer,
    fk_id_utilisateur integer
);


--
-- TOC entry 223 (class 1259 OID 17459)
-- Name: obs_livre_id_obs_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.obs_livre_id_obs_seq
    START WITH 3
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2507 (class 0 OID 0)
-- Dependencies: 223
-- Name: obs_livre_id_obs_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.obs_livre_id_obs_seq OWNED BY public.obs_livre.id_obs;


--
-- TOC entry 221 (class 1259 OID 17451)
-- Name: obs_name; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.obs_name (
    fk_id_name integer NOT NULL,
    id_obs integer NOT NULL
);


--
-- TOC entry 209 (class 1259 OID 16605)
-- Name: pays; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pays (
    nom character varying(255) NOT NULL,
    code character varying(4),
    date_creation timestamp(3) with time zone DEFAULT now() NOT NULL,
    date_modification timestamp(3) with time zone DEFAULT now() NOT NULL
);


--
-- TOC entry 210 (class 1259 OID 16610)
-- Name: recolte; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.recolte (
    id_recolte integer NOT NULL,
    fk_titre_mission integer,
    id_name integer,
    id_a integer,
    fk_id_loc integer,
    fk_pays character varying NOT NULL,
    fk_id_utilisateur smallint,
    fk_id_collecteur integer DEFAULT 1,
    ncoll character varying(255) DEFAULT NULL::character varying,
    nom_collecteur character varying(255) DEFAULT NULL::character varying,
    rem_determinations character varying(255) DEFAULT NULL::character varying,
    jj smallint,
    mm smallint,
    aaaa smallint,
    nom_provisoire_terrain character varying(255) DEFAULT NULL::character varying,
    localite_txt text,
    station text,
    altitude character varying(255) DEFAULT NULL::character varying,
    abondance character varying(255) DEFAULT NULL::character varying,
    date_confirm character varying(255) DEFAULT NULL::character varying,
    select_impression character varying(2) DEFAULT NULL::character varying,
    determinateur character varying(50) DEFAULT NULL::character varying,
    x_date_determinateur character varying(50) DEFAULT NULL::character varying,
    description text,
    codebarre character varying(50) DEFAULT NULL::character varying,
    phenologie character varying(50) DEFAULT NULL::character varying,
    biologie character varying(50) DEFAULT NULL::character varying,
    nb_parts integer,
    herbier_depot character varying(100) DEFAULT NULL::character varying,
    determ_originale character varying(200) DEFAULT NULL::character varying,
    herbier_collection character varying(50) DEFAULT NULL::character varying,
    remarques text,
    ref_origine text,
    type character(1) DEFAULT 0,
    date_creation timestamp(3) with time zone DEFAULT now() NOT NULL,
    date_modification timestamp(3) with time zone DEFAULT now() NOT NULL,
    uid character varying(40)
);


--
-- TOC entry 211 (class 1259 OID 16636)
-- Name: recolte_id_recolte_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.recolte_id_recolte_seq
    START WITH 154695
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2508 (class 0 OID 0)
-- Dependencies: 211
-- Name: recolte_id_recolte_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.recolte_id_recolte_seq OWNED BY public.recolte.id_recolte;


--
-- TOC entry 212 (class 1259 OID 16638)
-- Name: region; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.region (
    no_eco character varying(255) NOT NULL,
    division character varying(255) DEFAULT NULL::character varying,
    fk_pays character varying(255) DEFAULT NULL::character varying,
    bioclim character varying(255) DEFAULT NULL::character varying,
    domaine character varying(255) DEFAULT NULL::character varying,
    date_creation timestamp(3) with time zone DEFAULT now() NOT NULL,
    date_modification timestamp(3) with time zone DEFAULT now() NOT NULL
);


--
-- TOC entry 213 (class 1259 OID 16650)
-- Name: region_taxon; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.region_taxon (
    no_region_taxon integer NOT NULL,
    fk_no_eco character varying(255),
    id_name_a integer,
    nom_simple character varying(255) DEFAULT NULL::character varying,
    date_creation timestamp without time zone DEFAULT now() NOT NULL,
    abondance smallint
);


--
-- TOC entry 214 (class 1259 OID 16658)
-- Name: region_taxon_no_region_taxon_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.region_taxon_no_region_taxon_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2509 (class 0 OID 0)
-- Dependencies: 214
-- Name: region_taxon_no_region_taxon_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.region_taxon_no_region_taxon_seq OWNED BY public.region_taxon.no_region_taxon;


--
-- TOC entry 215 (class 1259 OID 16660)
-- Name: titre_missions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.titre_missions (
    no_titre_missions integer NOT NULL,
    titre text NOT NULL,
    date_creation timestamp(3) with time zone DEFAULT now() NOT NULL,
    date_modification timestamp(3) with time zone DEFAULT now() NOT NULL
);


--
-- TOC entry 216 (class 1259 OID 16668)
-- Name: titre_missions_no_titre_missions_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.titre_missions_no_titre_missions_seq
    START WITH 36
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2510 (class 0 OID 0)
-- Dependencies: 216
-- Name: titre_missions_no_titre_missions_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.titre_missions_no_titre_missions_seq OWNED BY public.titre_missions.no_titre_missions;


--
-- TOC entry 217 (class 1259 OID 16670)
-- Name: utilisateur_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.utilisateur_id_seq
    START WITH 4
    INCREMENT BY 1
    NO MINVALUE
    MAXVALUE 32767
    CACHE 1;


--
-- TOC entry 218 (class 1259 OID 16672)
-- Name: utilisateur; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.utilisateur (
    id smallint DEFAULT nextval('public.utilisateur_id_seq'::regclass) NOT NULL,
    login character varying(100),
    pwd character varying(255),
    role character varying(40),
    institut character varying(200),
    depot character varying(40),
    tentative smallint DEFAULT 0 NOT NULL,
    suspendre boolean DEFAULT false NOT NULL,
    secret character varying(200) DEFAULT NULL::character varying,
    date_creation timestamp(3) with time zone DEFAULT now() NOT NULL,
    date_modification timestamp(3) with time zone DEFAULT now() NOT NULL
);


--
-- TOC entry 2185 (class 2604 OID 16684)
-- Name: biblio no_bib; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.biblio ALTER COLUMN no_bib SET DEFAULT nextval('public.biblio_no_bib_seq'::regclass);


--
-- TOC entry 2192 (class 2604 OID 16685)
-- Name: collecteur id_coll; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.collecteur ALTER COLUMN id_coll SET DEFAULT nextval('public.collecteur_id_coll_seq'::regclass);


--
-- TOC entry 2201 (class 2604 OID 16686)
-- Name: distrib_biblio id_d; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.distrib_biblio ALTER COLUMN id_d SET DEFAULT nextval('public.distrib_biblio_id_d_seq'::regclass);


--
-- TOC entry 2265 (class 2604 OID 17458)
-- Name: livres id_livre_apd; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.livres ALTER COLUMN id_livre_apd SET DEFAULT nextval('public.livres_id_livres_apd_seq'::regclass);


--
-- TOC entry 2214 (class 2604 OID 16687)
-- Name: localite id_loc; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.localite ALTER COLUMN id_loc SET DEFAULT nextval('public.localite_id_loc_seq'::regclass);


--
-- TOC entry 2270 (class 2604 OID 17461)
-- Name: obs_livre id_obs; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.obs_livre ALTER COLUMN id_obs SET DEFAULT nextval('public.obs_livre_id_obs_seq'::regclass);


--
-- TOC entry 2246 (class 2604 OID 16688)
-- Name: recolte id_recolte; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recolte ALTER COLUMN id_recolte SET DEFAULT nextval('public.recolte_id_recolte_seq'::regclass);


--
-- TOC entry 2255 (class 2604 OID 16689)
-- Name: region_taxon no_region_taxon; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.region_taxon ALTER COLUMN no_region_taxon SET DEFAULT nextval('public.region_taxon_no_region_taxon_seq'::regclass);


--
-- TOC entry 2258 (class 2604 OID 16690)
-- Name: titre_missions no_titre_missions; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.titre_missions ALTER COLUMN no_titre_missions SET DEFAULT nextval('public.titre_missions_no_titre_missions_seq'::regclass);


--
-- TOC entry 2272 (class 2606 OID 16694)
-- Name: biblio biblio_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.biblio
    ADD CONSTRAINT biblio_pkey PRIMARY KEY (no_bib);


--
-- TOC entry 2274 (class 2606 OID 16696)
-- Name: cle cle_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cle
    ADD CONSTRAINT cle_pkey PRIMARY KEY (id_cle);


--
-- TOC entry 2280 (class 2606 OID 16698)
-- Name: collecteur collecteur_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.collecteur
    ADD CONSTRAINT collecteur_pkey PRIMARY KEY (id_coll);


--
-- TOC entry 2283 (class 2606 OID 16700)
-- Name: description description_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.description
    ADD CONSTRAINT description_pkey PRIMARY KEY (id_description);


--
-- TOC entry 2286 (class 2606 OID 16702)
-- Name: distrib_biblio distrib_biblio_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.distrib_biblio
    ADD CONSTRAINT distrib_biblio_pkey PRIMARY KEY (id_d);


--
-- TOC entry 2304 (class 2606 OID 16704)
-- Name: taxon id_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taxon
    ADD CONSTRAINT id_name_unique UNIQUE (id_name);


--
-- TOC entry 2297 (class 2606 OID 16706)
-- Name: image image_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.image
    ADD CONSTRAINT image_pkey PRIMARY KEY (id_image);


--
-- TOC entry 2301 (class 2606 OID 16708)
-- Name: localite localite_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.localite
    ADD CONSTRAINT localite_pkey PRIMARY KEY (id_loc);


--
-- TOC entry 2294 (class 2606 OID 16710)
-- Name: nom nom_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.nom
    ADD CONSTRAINT nom_pkey PRIMARY KEY (id_name);


--
-- TOC entry 2309 (class 2606 OID 16712)
-- Name: pays pays_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pays
    ADD CONSTRAINT pays_pkey PRIMARY KEY (nom);


--
-- TOC entry 2334 (class 2606 OID 17431)
-- Name: livres pk_livres; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.livres
    ADD CONSTRAINT pk_livres PRIMARY KEY (id_livre_apd);


--
-- TOC entry 2336 (class 2606 OID 17449)
-- Name: obs_livre pk_obs_livre; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.obs_livre
    ADD CONSTRAINT pk_obs_livre PRIMARY KEY (id_obs);


--
-- TOC entry 2338 (class 2606 OID 17455)
-- Name: obs_name pk_obs_name; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.obs_name
    ADD CONSTRAINT pk_obs_name PRIMARY KEY (fk_id_name, id_obs);


--
-- TOC entry 2321 (class 2606 OID 16714)
-- Name: recolte recolte_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recolte
    ADD CONSTRAINT recolte_pkey PRIMARY KEY (id_recolte);


--
-- TOC entry 2324 (class 2606 OID 16716)
-- Name: region region_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.region
    ADD CONSTRAINT region_pkey PRIMARY KEY (no_eco);


--
-- TOC entry 2326 (class 2606 OID 16718)
-- Name: region_taxon region_taxon_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.region_taxon
    ADD CONSTRAINT region_taxon_pkey PRIMARY KEY (no_region_taxon);


--
-- TOC entry 2307 (class 2606 OID 16720)
-- Name: taxon taxon_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taxon
    ADD CONSTRAINT taxon_pkey PRIMARY KEY (id_taxon);


--
-- TOC entry 2328 (class 2606 OID 16722)
-- Name: titre_missions titre_missions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.titre_missions
    ADD CONSTRAINT titre_missions_pkey PRIMARY KEY (no_titre_missions);


--
-- TOC entry 2330 (class 2606 OID 16724)
-- Name: utilisateur utilisateur_login_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utilisateur
    ADD CONSTRAINT utilisateur_login_key UNIQUE (login);


--
-- TOC entry 2332 (class 2606 OID 16726)
-- Name: utilisateur utilisateur_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utilisateur
    ADD CONSTRAINT utilisateur_pkey PRIMARY KEY (id);


--
-- TOC entry 2275 (class 1259 OID 16727)
-- Name: fki_cle_fk_id_taxon_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_cle_fk_id_taxon_fk ON public.cle USING btree (fk_id_taxon);


--
-- TOC entry 2295 (class 1259 OID 16728)
-- Name: fki_image_fk_id_taxon_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_image_fk_id_taxon_fk ON public.image USING btree (fk_id_taxon);


--
-- TOC entry 2298 (class 1259 OID 16729)
-- Name: fki_localite_fk__id_utilisateur_fkey; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_localite_fk__id_utilisateur_fkey ON public.localite USING btree (fk_id_utilisateur);


--
-- TOC entry 2302 (class 1259 OID 16730)
-- Name: fki_nom_fk_id_name_fkey; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_nom_fk_id_name_fkey ON public.taxon USING btree (id_name);


--
-- TOC entry 2310 (class 1259 OID 16731)
-- Name: fki_recolte_fk_id_collecteur_fkey; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_recolte_fk_id_collecteur_fkey ON public.recolte USING btree (fk_id_collecteur);


--
-- TOC entry 2311 (class 1259 OID 16732)
-- Name: fki_recolte_fk_id_loc_fkey; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_recolte_fk_id_loc_fkey ON public.recolte USING btree (fk_id_loc);


--
-- TOC entry 2312 (class 1259 OID 16733)
-- Name: fki_recolte_fk_id_utilisateur_fkey; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_recolte_fk_id_utilisateur_fkey ON public.recolte USING btree (fk_id_utilisateur);


--
-- TOC entry 2313 (class 1259 OID 16734)
-- Name: fki_recolte_fk_pays_fkey; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_recolte_fk_pays_fkey ON public.recolte USING btree (fk_pays);


--
-- TOC entry 2314 (class 1259 OID 16735)
-- Name: fki_recolte_id_a_fkey; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_recolte_id_a_fkey ON public.recolte USING btree (id_a);


--
-- TOC entry 2315 (class 1259 OID 16736)
-- Name: fki_recolte_id_name_fkey; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_recolte_id_name_fkey ON public.recolte USING btree (id_name);


--
-- TOC entry 2322 (class 1259 OID 16737)
-- Name: fki_recolte_pays; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_recolte_pays ON public.region USING btree (fk_pays);


--
-- TOC entry 2284 (class 1259 OID 16738)
-- Name: fki_taxon_fk_id_taxon_fk; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_taxon_fk_id_taxon_fk ON public.description USING btree (fk_id_taxon);


--
-- TOC entry 2316 (class 1259 OID 16739)
-- Name: index_aaaa; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX index_aaaa ON public.recolte USING btree (aaaa);


--
-- TOC entry 2317 (class 1259 OID 16740)
-- Name: index_codebarre; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX index_codebarre ON public.recolte USING btree (codebarre);


--
-- TOC entry 2276 (class 1259 OID 16741)
-- Name: index_droite_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX index_droite_index ON public.cle USING btree (droite);


--
-- TOC entry 2287 (class 1259 OID 16742)
-- Name: index_fk_id_a; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX index_fk_id_a ON public.nom USING btree (fk_id_a);


--
-- TOC entry 2288 (class 1259 OID 16743)
-- Name: index_fk_id_parent; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX index_fk_id_parent ON public.nom USING btree (fk_id_parent);


--
-- TOC entry 2299 (class 1259 OID 16744)
-- Name: index_full_name; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX index_full_name ON public.localite USING btree (full_name);


--
-- TOC entry 2277 (class 1259 OID 16745)
-- Name: index_gauche_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX index_gauche_index ON public.cle USING btree (gauche);


--
-- TOC entry 2289 (class 1259 OID 16746)
-- Name: index_genre; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX index_genre ON public.nom USING btree (genre);


--
-- TOC entry 2278 (class 1259 OID 16747)
-- Name: index_id_cle_uniq; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX index_id_cle_uniq ON public.cle USING btree (id_cle);


--
-- TOC entry 2290 (class 1259 OID 16748)
-- Name: index_id_genre; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX index_id_genre ON public.nom USING btree (fk_id_genre);


--
-- TOC entry 2318 (class 1259 OID 16749)
-- Name: index_ncoll; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX index_ncoll ON public.recolte USING btree (ncoll);


--
-- TOC entry 2291 (class 1259 OID 16750)
-- Name: index_no_rang; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX index_no_rang ON public.nom USING btree (no_rang);


--
-- TOC entry 2281 (class 1259 OID 16751)
-- Name: index_nom; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX index_nom ON public.collecteur USING btree (nom);


--
-- TOC entry 2319 (class 1259 OID 16752)
-- Name: index_nom_collecteur; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX index_nom_collecteur ON public.recolte USING btree (nom_collecteur);


--
-- TOC entry 2292 (class 1259 OID 16753)
-- Name: index_nom_standard; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX index_nom_standard ON public.nom USING btree (nom_standard);


--
-- TOC entry 2305 (class 1259 OID 16754)
-- Name: index_nom_standard_taxon; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX index_nom_standard_taxon ON public.taxon USING btree (nom_standard);


--
-- TOC entry 2358 (class 2620 OID 16755)
-- Name: biblio update_biblio_modtime; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_biblio_modtime BEFORE UPDATE ON public.biblio FOR EACH ROW EXECUTE PROCEDURE public.update_modified_column();


--
-- TOC entry 2359 (class 2620 OID 16756)
-- Name: collecteur update_collecteur_modtime; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_collecteur_modtime BEFORE UPDATE ON public.collecteur FOR EACH ROW EXECUTE PROCEDURE public.update_modified_column();


--
-- TOC entry 2360 (class 2620 OID 16757)
-- Name: description update_description_modtime; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_description_modtime BEFORE UPDATE ON public.description FOR EACH ROW EXECUTE PROCEDURE public.update_modified_column();


--
-- TOC entry 2361 (class 2620 OID 16758)
-- Name: distrib_biblio update_distrib_biblio_modtime; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_distrib_biblio_modtime BEFORE UPDATE ON public.distrib_biblio FOR EACH ROW EXECUTE PROCEDURE public.update_modified_column();


--
-- TOC entry 2372 (class 2620 OID 17450)
-- Name: obs_livre update_distrib_biblio_modtime; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_distrib_biblio_modtime BEFORE UPDATE ON public.obs_livre FOR EACH ROW EXECUTE PROCEDURE public.update_modified_column();


--
-- TOC entry 2363 (class 2620 OID 16759)
-- Name: image update_image_modtime; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_image_modtime BEFORE UPDATE ON public.image FOR EACH ROW EXECUTE PROCEDURE public.update_modified_column();


--
-- TOC entry 2364 (class 2620 OID 16760)
-- Name: localite update_localite_modtime; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_localite_modtime BEFORE UPDATE ON public.localite FOR EACH ROW EXECUTE PROCEDURE public.update_modified_column();


--
-- TOC entry 2362 (class 2620 OID 16761)
-- Name: nom update_nom_modtime; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_nom_modtime BEFORE UPDATE ON public.nom FOR EACH ROW EXECUTE PROCEDURE public.update_modified_column();


--
-- TOC entry 2365 (class 2620 OID 16762)
-- Name: nom_new update_nom_modtime; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_nom_modtime BEFORE UPDATE ON public.nom_new FOR EACH ROW EXECUTE PROCEDURE public.update_modified_column();


--
-- TOC entry 2366 (class 2620 OID 16763)
-- Name: pays update_pays_modtime; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_pays_modtime BEFORE UPDATE ON public.pays FOR EACH ROW EXECUTE PROCEDURE public.update_modified_column();


--
-- TOC entry 2367 (class 2620 OID 16764)
-- Name: recolte update_recolte_modtime; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_recolte_modtime BEFORE UPDATE ON public.recolte FOR EACH ROW EXECUTE PROCEDURE public.update_modified_column();


--
-- TOC entry 2368 (class 2620 OID 16765)
-- Name: region update_region_modtime; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_region_modtime BEFORE UPDATE ON public.region FOR EACH ROW EXECUTE PROCEDURE public.update_modified_column();


--
-- TOC entry 2369 (class 2620 OID 16766)
-- Name: region_taxon update_region_taxon_modtime; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_region_taxon_modtime BEFORE UPDATE ON public.region_taxon FOR EACH ROW EXECUTE PROCEDURE public.update_modified_column();


--
-- TOC entry 2370 (class 2620 OID 16767)
-- Name: titre_missions update_titre_missions_modtime; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_titre_missions_modtime BEFORE UPDATE ON public.titre_missions FOR EACH ROW EXECUTE PROCEDURE public.update_modified_column();


--
-- TOC entry 2371 (class 2620 OID 16768)
-- Name: utilisateur update_utilisateur_modtime; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_utilisateur_modtime BEFORE UPDATE ON public.utilisateur FOR EACH ROW EXECUTE PROCEDURE public.update_modified_column();


--
-- TOC entry 2339 (class 2606 OID 16769)
-- Name: description description_fk_id_taxon_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.description
    ADD CONSTRAINT description_fk_id_taxon_fk FOREIGN KEY (fk_id_taxon) REFERENCES public.taxon(id_taxon);


--
-- TOC entry 2340 (class 2606 OID 16774)
-- Name: distrib_biblio distrib_biblio_fk_id_loc_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.distrib_biblio
    ADD CONSTRAINT distrib_biblio_fk_id_loc_fkey FOREIGN KEY (fk_id_loc) REFERENCES public.localite(id_loc);


--
-- TOC entry 2341 (class 2606 OID 16779)
-- Name: distrib_biblio distrib_biblio_fk_id_name_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.distrib_biblio
    ADD CONSTRAINT distrib_biblio_fk_id_name_fkey FOREIGN KEY (fk_id_name) REFERENCES public.nom(id_name);


--
-- TOC entry 2342 (class 2606 OID 16784)
-- Name: distrib_biblio distrib_biblio_fk_no_bib_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.distrib_biblio
    ADD CONSTRAINT distrib_biblio_fk_no_bib_fkey FOREIGN KEY (fk_no_bib) REFERENCES public.biblio(no_bib);


--
-- TOC entry 2344 (class 2606 OID 16789)
-- Name: image image_fk_id_taxon_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.image
    ADD CONSTRAINT image_fk_id_taxon_fk FOREIGN KEY (fk_id_taxon) REFERENCES public.taxon(id_taxon);


--
-- TOC entry 2345 (class 2606 OID 16794)
-- Name: localite localite_fk__id_utilisateur_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.localite
    ADD CONSTRAINT localite_fk__id_utilisateur_fkey FOREIGN KEY (fk_id_utilisateur) REFERENCES public.utilisateur(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2346 (class 2606 OID 16799)
-- Name: localite localite_fk_pays_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.localite
    ADD CONSTRAINT localite_fk_pays_fkey FOREIGN KEY (fk_pays) REFERENCES public.pays(nom) ON UPDATE CASCADE;


--
-- TOC entry 2343 (class 2606 OID 16804)
-- Name: nom nom_fk_id_a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.nom
    ADD CONSTRAINT nom_fk_id_a FOREIGN KEY (fk_id_a) REFERENCES public.nom(id_name);


--
-- TOC entry 2347 (class 2606 OID 16809)
-- Name: taxon nom_fk_id_name_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taxon
    ADD CONSTRAINT nom_fk_id_name_fkey FOREIGN KEY (id_name) REFERENCES public.nom(id_name);


--
-- TOC entry 2348 (class 2606 OID 16814)
-- Name: recolte recolte_fk_id_collecteur_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recolte
    ADD CONSTRAINT recolte_fk_id_collecteur_fkey FOREIGN KEY (fk_id_collecteur) REFERENCES public.collecteur(id_coll) ON UPDATE CASCADE ON DELETE SET DEFAULT;


--
-- TOC entry 2349 (class 2606 OID 16819)
-- Name: recolte recolte_fk_id_loc_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recolte
    ADD CONSTRAINT recolte_fk_id_loc_fkey FOREIGN KEY (fk_id_loc) REFERENCES public.localite(id_loc);


--
-- TOC entry 2350 (class 2606 OID 16824)
-- Name: recolte recolte_fk_id_utilisateur_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recolte
    ADD CONSTRAINT recolte_fk_id_utilisateur_fkey FOREIGN KEY (fk_id_utilisateur) REFERENCES public.utilisateur(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 2351 (class 2606 OID 16829)
-- Name: recolte recolte_fk_pays_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recolte
    ADD CONSTRAINT recolte_fk_pays_fkey FOREIGN KEY (fk_pays) REFERENCES public.pays(nom);


--
-- TOC entry 2352 (class 2606 OID 16834)
-- Name: recolte recolte_fk_titre_mission_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recolte
    ADD CONSTRAINT recolte_fk_titre_mission_fkey FOREIGN KEY (fk_titre_mission) REFERENCES public.titre_missions(no_titre_missions);


--
-- TOC entry 2353 (class 2606 OID 16839)
-- Name: recolte recolte_id_a_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recolte
    ADD CONSTRAINT recolte_id_a_fkey FOREIGN KEY (id_a) REFERENCES public.nom(id_name);


--
-- TOC entry 2354 (class 2606 OID 16844)
-- Name: recolte recolte_id_name_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recolte
    ADD CONSTRAINT recolte_id_name_fkey FOREIGN KEY (id_name) REFERENCES public.nom(id_name);


--
-- TOC entry 2355 (class 2606 OID 16849)
-- Name: region region_fk_pays_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.region
    ADD CONSTRAINT region_fk_pays_fkey FOREIGN KEY (fk_pays) REFERENCES public.pays(nom) ON UPDATE CASCADE;


--
-- TOC entry 2356 (class 2606 OID 16854)
-- Name: region_taxon region_taxon_fk_no_eco_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.region_taxon
    ADD CONSTRAINT region_taxon_fk_no_eco_fkey FOREIGN KEY (fk_no_eco) REFERENCES public.region(no_eco) ON UPDATE CASCADE;


--
-- TOC entry 2357 (class 2606 OID 16859)
-- Name: region_taxon region_taxon_id_name_a_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.region_taxon
    ADD CONSTRAINT region_taxon_id_name_a_fkey FOREIGN KEY (id_name_a) REFERENCES public.nom(id_name);


-- Completed on 2019-04-12 11:14:31

--
-- PostgreSQL database dump complete
--

