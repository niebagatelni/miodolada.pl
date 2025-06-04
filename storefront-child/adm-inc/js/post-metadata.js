// Zamiast importować, użyj bezpośrednio wp.plugins i innych komponentów

const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel } = wp.editPost;
const { useSelect, useDispatch } = wp.data;

const AdmPostMetadataPanel = () => {
   // console.log("AdmPostMetadataPanel loaded");

    // Pobieramy dane z meta (słowa kluczowe, opis, inny tytuł)
    const meta = useSelect((select) => select("core/editor").getEditedPostAttribute("meta") || {}, []);
    const { editPost } = useDispatch("core/editor");

    // Funkcja zmieniająca wartości meta
    const handleChange = (metaKey, value) => {
        editPost({
            meta: {
                ...meta,
                [metaKey]: value,
            },
        });
    };

    return wp.element.createElement(
        wp.editPost.PluginDocumentSettingPanel, // Panel do edytora
        {
            name: "adm-post-metadata-panel",
            title: "Meta dane", // Tytuł panelu
            initialOpen: true,
        },
        wp.element.createElement(wp.components.TextControl, { // Bezpośrednie użycie komponentu wp.components
            label: "Słowa kluczowe", // Tytuł dla pola "Słowa kluczowe"
            value: meta["adm_post_keywords"] || "", // Przechowywana wartość
            onChange: (value) => handleChange("adm_post_keywords", value), // Zmiana wartości
        }),
        wp.element.createElement(wp.components.TextareaControl, { // Bezpośrednie użycie wp.components.TextareaControl
            label: "Opis", // Tytuł dla pola "Opis"
            value: meta["adm_post_description"] || "", // Przechowywana wartość
            onChange: (value) => handleChange("adm_post_description", value), // Zmiana wartości
            rows: 5, // Liczba wierszy dla textarea
        }),
        wp.element.createElement(wp.components.TextControl, { // Bezpośrednie użycie wp.components
            label: "Inny tytuł", // Tytuł dla pola "Inny tytuł"
            value: meta["adm_post_title"] || "", // Przechowywana wartość
            onChange: (value) => handleChange("adm_post_title", value), // Zmiana wartości
        })
    );
};

// Rejestracja pluginu
wp.plugins.registerPlugin("adm-post-metadata-panel", { render: AdmPostMetadataPanel });
