const $ = (q) => document.querySelector(q);

const state = {
  products: [],
  cart: new Map(),
  client: { id_cliente: 1, label: "Cliente contado" },
  clients: [],
};

function fmt(n){
  const x = Number(n || 0);
  return "₡" + x.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function today(){
  const d = new Date();
  const s = d.toLocaleString("es-CR", { weekday:"long", year:"numeric", month:"long", day:"numeric", hour:"2-digit", minute:"2-digit" });
  $("#todayChip").textContent = s;
}

async function getJSON(url){
  const r = await fetch(url, { credentials:"include" });
  const j = await r.json();
  if (!j.ok) throw new Error(j.error || "Error");
  return j.data;
}
async function postJSON(url, body){
  const r = await fetch(url, {
    method:"POST",
    headers:{ "Content-Type":"application/json" },
    body: JSON.stringify(body),
    credentials:"include"
  });
  const j = await r.json();
  if (!j.ok) throw new Error(j.error || "Error");
  return j.data;
}

function badgeForStock(s){
  if (s <= 3) return ["low", "Stock bajo: " + s];
  return ["ok", "Stock: " + s];
}

function renderProducts(){
  const list = $("#productsList");
  list.innerHTML = "";
  if (state.products.length === 0){
    list.innerHTML = `<div class="muted">No hay resultados.</div>`;
    return;
  }
  for (const p of state.products){
    const [cls, label] = badgeForStock(Number(p.stock));
    const el = document.createElement("div");
    el.className = "item";
    el.innerHTML = `
      <div>
        <div class="name">${escapeHtml(p.nombre)}</div>
        <div class="meta">${escapeHtml(p.sku || "")} · ${fmt(p.precio)}</div>
      </div>
      <div class="right">
        <div class="badge ${cls}">${label}</div>
        <button class="btn primary" data-add="${p.id_producto}">Agregar</button>
      </div>
    `;
    el.querySelector("[data-add]").addEventListener("click", () => addToCart(p));
    list.appendChild(el);
  }
}

function addToCart(p){
  const id = Number(p.id_producto);
  const cur = state.cart.get(id);
  const qty = cur ? cur.cantidad + 1 : 1;
  if (qty > Number(p.stock)) {
    flash("finishHint", "No podés agregar más: stock insuficiente.");
    return;
  }
  state.cart.set(id, { ...p, cantidad: qty });
  renderCart();
}

function decItem(id){
  const cur = state.cart.get(id);
  if (!cur) return;
  const qty = cur.cantidad - 1;
  if (qty <= 0) state.cart.delete(id);
  else state.cart.set(id, { ...cur, cantidad: qty });
  renderCart();
}
function incItem(id){
  const cur = state.cart.get(id);
  if (!cur) return;
  if (cur.cantidad + 1 > Number(cur.stock)) {
    flash("finishHint", "Stock insuficiente para subir cantidad.");
    return;
  }
  state.cart.set(id, { ...cur, cantidad: cur.cantidad + 1 });
  renderCart();
}
function removeItem(id){
  state.cart.delete(id);
  renderCart();
}

function renderCart(){
  const wrap = $("#cart");
  wrap.innerHTML = "";
  const items = Array.from(state.cart.values());
  if (items.length === 0){
    wrap.innerHTML = `<div class="muted">Agregá productos para iniciar.</div>`;
    $("#tSubtotal").textContent = fmt(0);
    $("#tTotal").textContent = fmt(0);
    return;
  }

  for (const it of items){
    const row = document.createElement("div");
    row.className = "cart-item";
    row.innerHTML = `
      <div>
        <div class="cname">${escapeHtml(it.nombre)}</div>
        <div class="cmeta">${escapeHtml(it.sku || "")} · ${fmt(it.precio)} · ${fmt(Number(it.precio) * Number(it.cantidad))}</div>
        <div class="cart-actions">
          <button class="btn danger" data-rm>Quitar</button>
        </div>
      </div>
      <div class="qty">
        <button data-dec>-</button>
        <div class="q">${it.cantidad}</div>
        <button data-inc>+</button>
      </div>
    `;
    row.querySelector("[data-dec]").addEventListener("click", () => decItem(Number(it.id_producto)));
    row.querySelector("[data-inc]").addEventListener("click", () => incItem(Number(it.id_producto)));
    row.querySelector("[data-rm]").addEventListener("click", () => removeItem(Number(it.id_producto)));
    wrap.appendChild(row);
  }
  calcTotals();
}

function calcTotals(){
  const items = Array.from(state.cart.values());
  let sub = 0;
  for (const it of items) sub += Number(it.precio) * Number(it.cantidad);
  $("#tSubtotal").textContent = fmt(sub);

  const disc = clampNum($("#discount").value);
  const tax = clampNum($("#tax").value);
  const tot = Math.max(0, sub - disc + tax);
  $("#tTotal").textContent = fmt(tot);
}

function clampNum(v){
  const n = Number(String(v).replaceAll(",", "."));
  if (!isFinite(n) || n < 0) return 0;
  return n;
}

function flash(id, msg){
  const el = $("#" + id);
  el.textContent = msg;
  clearTimeout(el._t);
  el._t = setTimeout(() => el.textContent = "", 3500);
}

function escapeHtml(s){
  return String(s ?? "").replace(/[&<>"']/g, m => ({ "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;" }[m]));
}

async function loadProducts(q=""){
  const data = await getJSON("/api/products.php?q=" + encodeURIComponent(q));
  state.products = data;
  renderProducts();
}

async function findClients(){
  const q = $("#qClient").value.trim();
  if (!q){ flash("finishHint","Escribí algo para buscar."); return; }
  const data = await getJSON("/api/clients.php?q=" + encodeURIComponent(q));
  state.clients = data;
  openClientMenu(data);
}

function openClientMenu(clients){
  const sel = $("#clientSelect");
  const menu = $("#clientMenu");
  menu.innerHTML = "";
  const base = [
    { id_cliente: 1, label: "Cliente contado" }
  ];

  const all = base.concat(clients.map(c => ({
    id_cliente: Number(c.id_cliente),
    label: [c.nombre, c.apellido].filter(Boolean).join(" ") + (c.telefono ? " · " + c.telefono : "")
  })));

  for (const c of all){
    const opt = document.createElement("div");
    opt.className = "select-opt";
    opt.textContent = c.label;
    opt.addEventListener("click", () => {
      state.client = { id_cliente: c.id_cliente, label: c.label };
      $("#clientValue").textContent = c.label;
      sel.classList.remove("open");
      flash("finishHint", "Cliente listo.");
    });
    menu.appendChild(opt);
  }
  sel.classList.add("open");
}

function bindSelectClose(){
  document.addEventListener("click", (e) => {
    const sel = $("#clientSelect");
    if (!sel.contains(e.target)) sel.classList.remove("open");
  });
  $("#clientValue").addEventListener("click", () => $("#clientSelect").classList.toggle("open"));
}

async function saveClient(){
  const form = $("#clientForm");
  const fd = new FormData(form);
  const payload = Object.fromEntries(fd.entries());
  try{
    const c = await postJSON("/api/clients.php", payload);
    state.client = { id_cliente: Number(c.id_cliente), label: [c.nombre, c.apellido].filter(Boolean).join(" ") };
    $("#clientValue").textContent = state.client.label;
    $("#dlgClient").close();
    flash("finishHint", "Cliente guardado.");
    form.reset();
  }catch(err){
    flash("clientHint", err.message);
  }
}

async function finish(){
  const items = Array.from(state.cart.values()).map(it => ({
    id_producto: Number(it.id_producto),
    cantidad: Number(it.cantidad)
  }));
  if (items.length === 0){ flash("finishHint", "Carrito vacío."); return; }

  const payload = {
    id_cliente: state.client.id_cliente === 1 ? 0 : state.client.id_cliente,
    descuento: clampNum($("#discount").value),
    impuesto: clampNum($("#tax").value),
    metodo_pago: $("#payMethod").value,
    observacion: $("#note").value.trim(),
    items
  };

  $("#btnFinish").disabled = true;
  try{
    const res = await postJSON("/api/finalize_sale.php", payload);
    state.cart.clear();
    renderCart();
    $("#discount").value = "0";
    $("#tax").value = "0";
    $("#note").value = "";
    flash("finishHint", "Venta lista: " + res.consecutivo);
    window.open("/invoice.php?id=" + encodeURIComponent(res.id_factura), "_blank");
    await loadProducts($("#qProduct").value.trim());
  }catch(err){
    flash("finishHint", err.message);
  }finally{
    $("#btnFinish").disabled = false;
  }
}

function init(){
  today();
  setInterval(today, 30000);

  $("#btnScan").addEventListener("click", () => loadProducts($("#qProduct").value.trim()));
  $("#qProduct").addEventListener("keydown", (e) => { if (e.key === "Enter") loadProducts($("#qProduct").value.trim()); });

  $("#discount").addEventListener("input", calcTotals);
  $("#tax").addEventListener("input", calcTotals);

  $("#btnFindClient").addEventListener("click", findClients);
  $("#qClient").addEventListener("keydown", (e) => { if (e.key === "Enter") findClients(); });

  $("#btnNewClient").addEventListener("click", () => $("#dlgClient").showModal());
  $("#btnSaveClient").addEventListener("click", (e) => { e.preventDefault(); saveClient(); });

  $("#btnFinish").addEventListener("click", finish);

  bindSelectClose();
  loadProducts();
  renderCart();
}

init();
