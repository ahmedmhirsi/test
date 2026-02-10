import React from 'react';
import { createRoot } from 'react-dom/client';
import htm from 'htm';
import { Tldraw } from 'tldraw';

const html = htm.bind(React.createElement);

function WhiteboardApp({ roomId }) {
    const [editor, setEditor] = React.useState(null);
    const persistenceKey = `tldraw-room-${roomId}`;

    // Load logic
    const loadFromDatabase = React.useCallback(async (app) => {
        try {
            const response = await fetch(`/collaboration/whiteboard/${roomId}/load`);
            const data = await response.json();
            if (data.success && data.canvas_data) {
                app.loadSnapshot(JSON.parse(data.canvas_data));
            }
        } catch (e) { console.error("Load failed", e); }
    }, [roomId]);

    // Save logic
    const saveToDatabase = React.useCallback(async (snapshot) => {
        try {
            await fetch(`/collaboration/whiteboard/${roomId}/save`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(snapshot)
            });
        } catch (e) { console.error("Save failed", e); }
    }, [roomId]);

    const handleMount = React.useCallback((app) => {
        setEditor(app);
        loadFromDatabase(app);
    }, [loadFromDatabase]);

    // Debounced Sync
    React.useEffect(() => {
        if (!editor) return;
        let timeout;
        const cleanup = editor.store.listen(() => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                saveToDatabase(editor.getSnapshot());
            }, 1500);
        });
        return () => { cleanup(); clearTimeout(timeout); };
    }, [editor, saveToDatabase]);

    return html`
        <div style=${{ position: 'fixed', inset: 0, top: '64px' }}>
            <${Tldraw} onMount=${handleMount} persistenceKey=${persistenceKey} />
        </div>
    `;
}

// Manual Mount logic
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('whiteboard-root');
    if (container) {
        const roomId = container.dataset.roomId;
        const root = createRoot(container);
        root.render(html`<${WhiteboardApp} roomId=${roomId} />`);
    }
});
