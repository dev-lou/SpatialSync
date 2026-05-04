/**
 * SpatialSync Blueprint Exporter
 * Converts 3D build data -> 2D architectural floor plan PDF
 * 1 unit = 1 metre | Scale: 1:100 default
 */
class BlueprintExporter {
    constructor(editor) {
        this.editor = editor;
        // Canvas resolution: pixels per metre at 1:100 on A4 (210mm wide = ~794px at 96dpi)
        this.PX_PER_METRE = 40; // at 1:100
        this.PADDING = 60; // px padding inside canvas
        this.CANVAS_W = 1200;
        this.CANVAS_H = 900;
    }

    // ── PUBLIC ENTRY POINT ──────────────────────────────────────────────
    async export(options = {}) {
        const {
            paperSize   = 'a4',
            scale       = 100,        // 1:scale
            floors      = 'all',      // 'all' | number
            furniture   = true,
            dimensions  = true,
            buildName   = 'Blueprint'
        } = options;

        // Scale pixels per metre
        this.PX_PER_METRE = Math.round(4000 / scale);

        // Group parts by floor
        const floorMap = this._groupByFloor();
        const floorNums = floors === 'all'
            ? [...floorMap.keys()].sort((a, b) => a - b)
            : [Number(floors)];

        // Init jsPDF (landscape A4 or A3)
        const isA3 = paperSize === 'a3';
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: paperSize });

        for (let i = 0; i < floorNums.length; i++) {
            const floorNum = floorNums[i];
            const parts = floorMap.get(floorNum) || [];

            if (i > 0) doc.addPage();

            const canvas = this._renderFloor(parts, floorNum, { furniture, dimensions, scale, buildName });
            const imgData = canvas.toDataURL('image/png', 1.0);

            const pw = doc.internal.pageSize.getWidth();
            const ph = doc.internal.pageSize.getHeight();

            // Draw floor plan image (leave 25mm at bottom for title block)
            doc.addImage(imgData, 'PNG', 5, 5, pw - 10, ph - 32);

            // Draw title block
            this._drawTitleBlock(doc, buildName, floorNum, scale, paperSize);
        }

        const filename = `${buildName.replace(/\s+/g, '_')}_Blueprint.pdf`;
        doc.save(filename);
    }

    // ── GROUP PARTS ──────────────────────────────────────────────────────
    _groupByFloor() {
        const map = new Map();
        this.editor.parts.forEach((partEntry) => {
            const data = partEntry.data || partEntry.mesh?.userData;
            if (!data) return;
            const floor = data.floor_number || 1;
            if (!map.has(floor)) map.set(floor, []);
            map.get(floor).push({
                ...data,
                px: partEntry.mesh?.position?.x ?? data.position_x ?? 0,
                py: partEntry.mesh?.position?.y ?? data.position_y ?? 0,
                pz: partEntry.mesh?.position?.z ?? data.position_z ?? 0,
                ry: (partEntry.mesh?.rotation?.y ?? 0) * (180 / Math.PI)
            });
        });
        return map;
    }

    // ── RENDER ONE FLOOR ─────────────────────────────────────────────────
    _renderFloor(parts, floorNum, opts) {
        const canvas = document.createElement('canvas');
        canvas.width  = this.CANVAS_W;
        canvas.height = this.CANVAS_H;
        const ctx = canvas.getContext('2d');

        // Background: architectural white with light grid
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        this._drawGrid(ctx);

        // Compute bounding box for centering
        const bounds = this._getBounds(parts);
        const offsetX = this.PADDING + (this.CANVAS_W - 2 * this.PADDING - bounds.w * this.PX_PER_METRE) / 2 - bounds.minX * this.PX_PER_METRE;
        const offsetZ = this.PADDING + (this.CANVAS_H - 2 * this.PADDING - bounds.d * this.PX_PER_METRE) / 2 - bounds.minZ * this.PX_PER_METRE;

        const toX = (wx) => offsetX + wx * this.PX_PER_METRE;
        const toY = (wz) => offsetZ + wz * this.PX_PER_METRE;

        // Draw order: floors → walls → openings → furniture
        const FLOOR_TYPES    = ['floor'];
        const WALL_TYPES     = ['wall', 'structural'];
        const OPENING_TYPES  = ['door', 'window'];
        const FURNITURE_TYPES = ['furniture', 'fixture', 'stairs', 'landscape'];

        this._drawParts(ctx, parts.filter(p => FLOOR_TYPES.includes(p.type)), toX, toY, opts);
        this._drawParts(ctx, parts.filter(p => WALL_TYPES.includes(p.type)), toX, toY, opts);
        this._drawParts(ctx, parts.filter(p => OPENING_TYPES.includes(p.type)), toX, toY, opts);
        if (opts.furniture) {
            this._drawParts(ctx, parts.filter(p => FURNITURE_TYPES.includes(p.type)), toX, toY, opts);
        }

        // Dimension lines
        if (opts.dimensions && bounds.w > 0) {
            this._drawDimensions(ctx, bounds, offsetX, offsetZ);
        }

        // Floor label
        ctx.font = 'bold 18px "Arial", sans-serif';
        ctx.fillStyle = '#1E293B';
        ctx.fillText(`FLOOR ${floorNum} - FLOOR PLAN`, 20, 28);

        ctx.font = '13px "Arial", sans-serif';
        ctx.fillStyle = '#64748B';
        ctx.fillText(`Scale 1:${opts.scale}  |  1 unit = 1 metre`, 20, 48);

        // North arrow
        this._drawNorthArrow(ctx, canvas.width - 60, 60);

        return canvas;
    }

    // ── BACKGROUND GRID ──────────────────────────────────────────────────
    _drawGrid(ctx) {
        const step = this.PX_PER_METRE;
        ctx.strokeStyle = '#E2E8F0';
        ctx.lineWidth = 0.5;
        for (let x = 0; x < this.CANVAS_W; x += step) {
            ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, this.CANVAS_H); ctx.stroke();
        }
        for (let y = 0; y < this.CANVAS_H; y += step) {
            ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(this.CANVAS_W, y); ctx.stroke();
        }
    }

    // ── DRAW ALL PARTS OF A TYPE LIST ────────────────────────────────────
    _drawParts(ctx, parts, toX, toY, opts) {
        parts.forEach(p => {
            const cx = toX(p.px);
            const cz = toY(p.pz);
            const w  = (p.width  || 1) * this.PX_PER_METRE;
            const d  = (p.depth  || 1) * this.PX_PER_METRE;
            const ry = p.ry || 0;
            const isRotated = Math.abs(ry % 180) > 45;

            const drawW = isRotated ? d : w;
            const drawD = isRotated ? w : d;

            ctx.save();
            ctx.translate(cx, cz);

            switch (p.type) {
                case 'floor':   this._renderFloorTile(ctx, drawW, drawD, p); break;
                case 'wall':    this._renderWall(ctx, drawW, drawD, p); break;
                case 'door':    this._renderDoor(ctx, drawW, drawD, p, ry); break;
                case 'window':  this._renderWindow(ctx, drawW, drawD, p); break;
                case 'structural': this._renderStructural(ctx, drawW, drawD, p); break;
                case 'stairs':  this._renderStairs(ctx, drawW, drawD); break;
                default:        this._renderFurniture(ctx, drawW, drawD, p); break;
            }
            ctx.restore();
        });
    }

    // ── FLOOR TILE ────────────────────────────────────────────────────────
    _renderFloorTile(ctx, w, d, p) {
        ctx.fillStyle = '#F1F5F9';
        ctx.strokeStyle = '#CBD5E1';
        ctx.lineWidth = 0.5;
        ctx.fillRect(-w/2, -d/2, w, d);
        ctx.strokeRect(-w/2, -d/2, w, d);
    }

    // ── WALL ──────────────────────────────────────────────────────────────
    _renderWall(ctx, w, d, p) {
        ctx.fillStyle = '#1E293B';
        ctx.strokeStyle = '#0F172A';
        ctx.lineWidth = 1;
        // walls are thin — architectural convention shows them as thick filled rectangles
        const thickness = Math.max(d, 4);
        ctx.fillRect(-w/2, -thickness/2, w, thickness);
    }

    // ── DOOR (quarter arc) ────────────────────────────────────────────────
    _renderDoor(ctx, w, d, p, ry) {
        const r = w;
        // Opening gap (clear white)
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(-w/2, -4, w, 8);

        // Door leaf
        ctx.strokeStyle = '#1E293B';
        ctx.lineWidth = 2;
        ctx.beginPath(); ctx.moveTo(-w/2, 0); ctx.lineTo(w/2, 0); ctx.stroke();

        // Arc swing
        ctx.beginPath();
        ctx.arc(-w/2, 0, r, 0, Math.PI / 2);
        ctx.strokeStyle = '#64748B';
        ctx.lineWidth = 1;
        ctx.setLineDash([3, 3]);
        ctx.stroke();
        ctx.setLineDash([]);

        // Swing end line
        ctx.strokeStyle = '#1E293B';
        ctx.lineWidth = 1;
        ctx.beginPath(); ctx.moveTo(-w/2, 0); ctx.lineTo(-w/2, r); ctx.stroke();
    }

    // ── WINDOW ────────────────────────────────────────────────────────────
    _renderWindow(ctx, w, d, p) {
        const gap = 6;
        // Clear gap in wall
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(-w/2, -gap, w, gap * 2);

        // Three parallel lines = standard window notation
        ctx.strokeStyle = '#1E293B';
        ctx.lineWidth = 1.5;
        [-gap/2, 0, gap/2].forEach(offset => {
            ctx.beginPath();
            ctx.moveTo(-w/2, offset);
            ctx.lineTo(w/2, offset);
            ctx.stroke();
        });
    }

    // ── STRUCTURAL (column) ───────────────────────────────────────────────
    _renderStructural(ctx, w, d, p) {
        ctx.fillStyle = '#334155';
        ctx.strokeStyle = '#0F172A';
        ctx.lineWidth = 1;
        if ((p.variant || '').includes('column')) {
            // Solid square/circle
            ctx.fillRect(-w/2, -d/2, w, d);
            // Diagonal hatching
            ctx.strokeStyle = '#FFFFFF';
            ctx.lineWidth = 0.8;
            ctx.beginPath(); ctx.moveTo(-w/2, -d/2); ctx.lineTo(w/2, d/2); ctx.stroke();
            ctx.beginPath(); ctx.moveTo(w/2, -d/2); ctx.lineTo(-w/2, d/2); ctx.stroke();
        } else {
            // Beam - thin rectangle
            ctx.fillRect(-w/2, -2, w, 4);
        }
    }

    // ── STAIRS ────────────────────────────────────────────────────────────
    _renderStairs(ctx, w, d) {
        const steps = 6;
        const stepH = d / steps;
        ctx.strokeStyle = '#475569';
        ctx.lineWidth = 1;
        for (let i = 0; i <= steps; i++) {
            ctx.beginPath();
            ctx.moveTo(-w/2, -d/2 + i * stepH);
            ctx.lineTo(w/2,  -d/2 + i * stepH);
            ctx.stroke();
        }
        ctx.fillRect(-w/2, -d/2, w, d); // outline
        ctx.strokeStyle = '#475569';
        ctx.strokeRect(-w/2, -d/2, w, d);

        // Arrow direction
        ctx.strokeStyle = '#0F172A';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(0, d/2 - 4);
        ctx.lineTo(0, -d/2 + 4);
        ctx.lineTo(-5, -d/2 + 12);
        ctx.moveTo(0, -d/2 + 4);
        ctx.lineTo(5, -d/2 + 12);
        ctx.stroke();
    }

    // ── FURNITURE (dashed outline) ────────────────────────────────────────
    _renderFurniture(ctx, w, d, p) {
        ctx.strokeStyle = '#94A3B8';
        ctx.lineWidth = 1;
        ctx.setLineDash([4, 3]);
        ctx.strokeRect(-w/2, -d/2, w, d);
        ctx.setLineDash([]);

        // Label
        if (w > 20 && d > 10) {
            ctx.font = `${Math.min(10, w / 4)}px Arial`;
            ctx.fillStyle = '#94A3B8';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            const label = (p.variant || p.type || '').substring(0, 6);
            ctx.fillText(label, 0, 0);
        }
    }

    // ── DIMENSION LINES ───────────────────────────────────────────────────
    _drawDimensions(ctx, bounds, offsetX, offsetZ) {
        const p = this.PX_PER_METRE;
        const x1 = offsetX + bounds.minX * p;
        const x2 = offsetX + bounds.maxX * p;
        const z1 = offsetZ + bounds.minZ * p;
        const z2 = offsetZ + bounds.maxZ * p;

        ctx.strokeStyle = '#3B82F6';
        ctx.fillStyle   = '#3B82F6';
        ctx.lineWidth   = 1;
        ctx.font = '11px Arial';
        ctx.textAlign = 'center';

        // Horizontal dimension (below plan)
        const dimY = z2 + 30;
        this._dimLine(ctx, x1, dimY, x2, dimY, `${bounds.w.toFixed(1)} m`);

        // Vertical dimension (left of plan)
        const dimX = x1 - 30;
        this._dimLine(ctx, dimX, z1, dimX, z2, `${bounds.d.toFixed(1)} m`, true);
    }

    _dimLine(ctx, x1, y1, x2, y2, label, vertical = false) {
        const arrowSize = 6;
        ctx.beginPath(); ctx.moveTo(x1, y1); ctx.lineTo(x2, y2); ctx.stroke();

        // Arrow heads
        if (!vertical) {
            ctx.beginPath(); ctx.moveTo(x1, y1); ctx.lineTo(x1 + arrowSize, y1 - arrowSize/2); ctx.lineTo(x1 + arrowSize, y1 + arrowSize/2); ctx.closePath(); ctx.fill();
            ctx.beginPath(); ctx.moveTo(x2, y2); ctx.lineTo(x2 - arrowSize, y2 - arrowSize/2); ctx.lineTo(x2 - arrowSize, y2 + arrowSize/2); ctx.closePath(); ctx.fill();
            ctx.fillText(label, (x1+x2)/2, y1 - 8);
        } else {
            ctx.beginPath(); ctx.moveTo(x1, y1); ctx.lineTo(x1 - arrowSize/2, y1 + arrowSize); ctx.lineTo(x1 + arrowSize/2, y1 + arrowSize); ctx.closePath(); ctx.fill();
            ctx.beginPath(); ctx.moveTo(x2, y2); ctx.lineTo(x2 - arrowSize/2, y2 - arrowSize); ctx.lineTo(x2 + arrowSize/2, y2 - arrowSize); ctx.closePath(); ctx.fill();
            ctx.save(); ctx.translate(x1 - 8, (y1+y2)/2); ctx.rotate(-Math.PI/2);
            ctx.fillText(label, 0, 0); ctx.restore();
        }
    }

    // ── NORTH ARROW ───────────────────────────────────────────────────────
    _drawNorthArrow(ctx, x, y) {
        const r = 20;
        ctx.strokeStyle = '#1E293B'; ctx.fillStyle = '#1E293B'; ctx.lineWidth = 1.5;
        // Circle
        ctx.beginPath(); ctx.arc(x, y, r, 0, 2*Math.PI); ctx.stroke();
        // Arrow
        ctx.beginPath(); ctx.moveTo(x, y - r + 4); ctx.lineTo(x - 6, y + 4); ctx.lineTo(x, y - 2); ctx.closePath(); ctx.fill();
        ctx.beginPath(); ctx.moveTo(x, y - r + 4); ctx.lineTo(x + 6, y + 4); ctx.lineTo(x, y - 2); ctx.closePath(); ctx.stroke();
        // N label
        ctx.font = 'bold 12px Arial'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.fillText('N', x, y + 10);
    }

    // ── TITLE BLOCK ───────────────────────────────────────────────────────
    _drawTitleBlock(doc, buildName, floorNum, scale, paperSize) {
        const pw = doc.internal.pageSize.getWidth();
        const ph = doc.internal.pageSize.getHeight();
        const tbY = ph - 26;

        // Border
        doc.setDrawColor(30, 41, 59);
        doc.setLineWidth(0.5);
        doc.rect(5, tbY, pw - 10, 21);

        // Dividers
        const col1 = pw * 0.35;
        const col2 = pw * 0.55;
        const col3 = pw * 0.75;
        doc.line(col1, tbY, col1, ph - 5);
        doc.line(col2, tbY, col2, ph - 5);
        doc.line(col3, tbY, col3, ph - 5);

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(9);
        doc.setTextColor(30, 41, 59);

        doc.text('PROJECT', 7, tbY + 5);
        doc.text('FLOOR', col1 + 2, tbY + 5);
        doc.text('SCALE', col2 + 2, tbY + 5);
        doc.text('DATE', col3 + 2, tbY + 5);

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(11);
        doc.text(buildName.toUpperCase().substring(0, 35), 7, tbY + 16);

        doc.setFontSize(10);
        const floorLabel = floorNum === 1 ? 'Ground Floor' : `Floor ${floorNum}`;
        doc.text(floorLabel, col1 + 2, tbY + 16);
        doc.text(`1 : ${scale}`, col2 + 2, tbY + 16);
        doc.text(new Date().toLocaleDateString('en-GB'), col3 + 2, tbY + 16);

        // SpatialSync watermark
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(7);
        doc.setTextColor(148, 163, 184);
        doc.text('Generated by SpatialSync', pw - 7, tbY + 16, { align: 'right' });
    }

    // ── HELPERS ───────────────────────────────────────────────────────────
    _getBounds(parts) {
        if (!parts.length) return { minX: 0, maxX: 10, minZ: 0, maxZ: 10, w: 10, d: 10 };
        let minX = Infinity, maxX = -Infinity, minZ = Infinity, maxZ = -Infinity;
        parts.forEach(p => {
            const hw = (p.width || 1) / 2;
            const hd = (p.depth || 1) / 2;
            minX = Math.min(minX, p.px - hw);
            maxX = Math.max(maxX, p.px + hw);
            minZ = Math.min(minZ, p.pz - hd);
            maxZ = Math.max(maxZ, p.pz + hd);
        });
        return { minX, maxX, minZ, maxZ, w: maxX - minX, d: maxZ - minZ };
    }
}

window.BlueprintExporter = BlueprintExporter;
