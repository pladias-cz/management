import autoComplete from "@tarekraafat/autocomplete.js";
import "@tarekraafat/autocomplete.js/dist/css/autoComplete.css";

import * as PladiasServer from "./pladias_server";

export default function prepareForm() {

    const initAutocomplete = (input) => {
        if (input.dataset.acInit) return; // zabrání opakované inicializaci
        input.dataset.acInit = "1";

        let controller;

        new autoComplete({
            selector: () => input,
            threshold: 2,
            debounce: 300,

            data: {
                src: async (query) => {
                    if (controller) controller.abort();
                    controller = new AbortController();

                    const url = PladiasServer.getAppBasePath()
                        + input.dataset.source
                        + "?term=" + encodeURIComponent(query);

                    const response = await fetch(url, {
                        signal: controller.signal
                    });

                    return await response.json();
                },
                keys: ["label", "value", "name"]
            },

            resultsList: {
                element: (list) => {
                    list.style.zIndex = "1000";
                }
            },

            events: {
                input: {
                    selection: (event) => {
                        const item = event.detail.selection.value;
                        // použijeme value > label > name jako hodnotu do inlineAdd inputu
                        const value = item.value ?? item.label ?? item.name;
                        input.value = value;
                    }
                }
            }
        });
    };

    // inicializace existujících inputů
    document.querySelectorAll(".autocomplete-edit").forEach(initAutocomplete);

    // sledování DOM pro dynamicky přidané inlineAdd inputy
    const observer = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType !== 1) return; // jen elementy
                if (node.matches(".autocomplete-edit")) {
                    initAutocomplete(node);
                }
                node.querySelectorAll && node.querySelectorAll(".autocomplete-edit").forEach(initAutocomplete);
            });
        }
    });

    observer.observe(document.body, { childList: true, subtree: true });
}
