// realidad_aumentada_3d.js
// Lightweight 3D customizer using Three.js + GLTFLoader.
// Features:
// - Load a GLB model (assumes the model has a material using a map / supports UVs)
// - Create a large canvas texture used as the garment's "print" texture
// - Allow changing base color (tint) of the garment material
// - Place text or image decals by clicking the model (uses uv coordinates)
// - Simple preview and export of the final texture

(function(){
  // Public API anchor
  window.RA3D = {};

  const container = document.body;
  let scene, camera, renderer, controls, raycaster, mouse;
  let model, garmentMesh, baseMaterial;
  const canvasSize = 2048; // large for reasonable print quality
  const decalCanvas = document.createElement('canvas');
  decalCanvas.width = canvasSize;
  decalCanvas.height = canvasSize;
  const dctx = decalCanvas.getContext('2d');

  // default: transparent background so base color shows through
  dctx.clearRect(0,0,canvasSize,canvasSize);

  // Three texture from canvas
  const decalTexture = new THREE.CanvasTexture(decalCanvas);
  decalTexture.flipY = false; // GLTF UVs usually expect non-flipped textures
  decalTexture.encoding = THREE.sRGBEncoding;

  // state
  RA3D.previewText = null; // {text, size}
  RA3D.placingMode = null; // 'text' | 'image' | null
  RA3D.uploadedImage = null; // Image object

  init();
  animate();

  function init(){
    // Renderer
    renderer = new THREE.WebGLRenderer({antialias:true});
    renderer.setSize(window.innerWidth, window.innerHeight - 0);
    renderer.setPixelRatio(window.devicePixelRatio || 1);
    renderer.outputEncoding = THREE.sRGBEncoding;
    container.appendChild(renderer.domElement);

    // Scene & Camera
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x111111);
    camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.set(0, 1.6, 2.5);

    // Lights
    const hemi = new THREE.HemisphereLight(0xffffff, 0x444444, 0.8);
    hemi.position.set(0, 2, 0);
    scene.add(hemi);
    const dir = new THREE.DirectionalLight(0xffffff, 0.8);
    dir.position.set(5, 10, 7.5);
    scene.add(dir);

    // Controls
    controls = new THREE.OrbitControls(camera, renderer.domElement);
    controls.target.set(0,1,0);
    controls.update();

    // Raycaster
    raycaster = new THREE.Raycaster();
    mouse = new THREE.Vector2();

    // Load model
    const loader = new THREE.GLTFLoader();
    const modelPath = 'oversized_t-shirt.glb'; // relative to index.html
    loader.load(modelPath, gltf => {
      model = gltf.scene;
      model.traverse(node => {
        if (node.isMesh) {
          // choose the first mesh as garmentMesh if none set
          if (!garmentMesh) {
            garmentMesh = node;
            setupGarmentMaterial(garmentMesh);
          }
          // enable cast/receive
          node.castShadow = true;
          node.receiveShadow = true;
        }
      });
      scene.add(model);
      centerModel();
    }, xhr => {
      // progress
      //console.log('loading', (xhr.loaded/xhr.total)*100);
    }, err => {
      console.error('GLTF load error', err);
    });

    window.addEventListener('resize', onWindowResize);
    renderer.domElement.addEventListener('pointerdown', onPointerDown);

    // small UI: create controls for placing text/image and upload
    createUI();
  }

  function setupGarmentMaterial(mesh){
    // If mesh material uses a map, preserve it; otherwise we create a standard material
    const mat = Array.isArray(mesh.material) ? mesh.material[0] : mesh.material;
    // Create a clone so we don't overwrite original gltf materials
    baseMaterial = mat.clone ? mat.clone() : new THREE.MeshStandardMaterial();

    // We'll use decalTexture as a secondary map (emissiveMap) to show prints
    baseMaterial.map = baseMaterial.map || null; // keep base fabric texture if present
    // Create a combined material using an alpha map approach: we'll use decalTexture as a separate layer
    // Approach: use an additional material map in emissiveMap so decals appear clearly
    baseMaterial.emissive = new THREE.Color(0xffffff);
    baseMaterial.emissiveMap = decalTexture;
    baseMaterial.emissiveIntensity = 1.0;

    // Ensure material is updated when canvas changes
    baseMaterial.needsUpdate = true;

    if (Array.isArray(mesh.material)){
      mesh.material = mesh.material.map(m=> baseMaterial);
    } else {
      mesh.material = baseMaterial;
    }

    // initialize base color
    setBaseColor('#ffffff');
  }

  function centerModel(){
    const box = new THREE.Box3().setFromObject(model);
    const center = box.getCenter(new THREE.Vector3());
    model.position.sub(center);
    model.position.y += 0.8; // lift a bit
  }

  function onWindowResize(){
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
  }

  function animate(){
    requestAnimationFrame(animate);
    renderer.render(scene, camera);
  }

  function onPointerDown(event){
    if (!RA3D.placingMode) return; // only active when placing

    const rect = renderer.domElement.getBoundingClientRect();
    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

    raycaster.setFromCamera(mouse, camera);
    const intersects = raycaster.intersectObject(model, true);

    if (intersects.length > 0){
      const it = intersects[0];
      if (it.uv && it.uv.x !== undefined){
        const uv = it.uv; // THREE.Vector2
        placeOnUV(uv);
      } else {
        console.warn('No UV coordinates on intersected face. The mesh must have UVs for decals.');
      }
    }
  }

  function placeOnUV(uv){
    // uv are 0..1 coordinates. Convert to canvas pixels
    const x = Math.floor(uv.x * canvasSize);
    const y = Math.floor((1 - uv.y) * canvasSize); // flip Y for canvas coords

    if (RA3D.placingMode === 'text' && RA3D.previewText){
      drawTextAt(RA3D.previewText.text, RA3D.previewText.size, x, y);
    } else if (RA3D.placingMode === 'image' && RA3D.uploadedImage){
      drawImageAt(RA3D.uploadedImage, x, y);
    }

    // refresh texture
    decalTexture.needsUpdate = true;
  }

  function drawTextAt(text, size, cx, cy){
    const fontSize = (size === 'small') ? 80 : (size === 'large' ? 220 : 140);
    dctx.save();

    // apply drop shadow
    dctx.font = `${fontSize}px sans-serif`;
    dctx.textAlign = 'center';
    dctx.textBaseline = 'middle';
    dctx.fillStyle = '#000000';
    dctx.lineWidth = 8;
    dctx.strokeStyle = 'rgba(0,0,0,0.6)';
    dctx.strokeText(text, cx, cy);
    dctx.fillStyle = '#ffffff';
    dctx.fillText(text, cx, cy);
    dctx.restore();
  }

  function drawImageAt(img, cx, cy){
    const w = Math.floor(canvasSize * 0.25);
    const h = Math.floor((img.height / img.width) * w);
    dctx.save();
    dctx.translate(cx, cy);
    dctx.drawImage(img, -w/2, -h/2, w, h);
    dctx.restore();
  }

  function createUI(){
    // small floating panel
    const panel = document.createElement('div');
    panel.style.position = 'fixed';
    panel.style.right = '12px';
    panel.style.top = '80px';
    panel.style.background = 'rgba(20,20,20,0.9)';
    panel.style.color = '#fff';
    panel.style.padding = '12px';
    panel.style.borderRadius = '8px';
    panel.style.zIndex = '9999';
    panel.style.width = '220px';
    panel.style.fontFamily = 'sans-serif';
    panel.innerHTML = `
      <div style="font-weight:700;margin-bottom:6px">Customizer</div>
      <div style="margin-bottom:6px">Base color:</div>
      <input id="ra_color" type="color" value="#ffffff" style="width:100%;height:34px;border:none;margin-bottom:8px;" />
      <div style="margin-bottom:6px">Text to place:</div>
      <input id="ra_text" type="text" placeholder="Mi frase" style="width:100%;margin-bottom:6px;" />
      <select id="ra_text_size" style="width:100%;margin-bottom:6px;">
        <option value="small">Pequeño</option>
        <option value="medium" selected>Medio</option>
        <option value="large">Grande</option>
      </select>
      <button id="ra_place_text" style="width:100%;margin-bottom:6px;">Place Text (click model)</button>
      <div style="margin:6px 0;border-top:1px solid rgba(255,255,255,0.06)"></div>
      <div style="margin-bottom:6px">Upload image:</div>
      <input id="ra_image_file" type="file" accept="image/*" style="width:100%;margin-bottom:6px;" />
      <button id="ra_place_image" style="width:100%;margin-bottom:6px;">Place Image (click model)</button>
      <div style="margin:6px 0;border-top:1px solid rgba(255,255,255,0.06)"></div>
      <button id="ra_clear" style="width:100%;margin-bottom:6px;">Clear Prints</button>
      <button id="ra_export" style="width:100%;margin-top:6px;">Export Texture</button>
    `;

    document.body.appendChild(panel);

    // wire up
    document.getElementById('ra_color').addEventListener('input', (e)=>{
      setBaseColor(e.target.value);
    });
    document.getElementById('ra_place_text').addEventListener('click', ()=>{
      RA3D.placingMode = 'text';
      RA3D.previewText = { text: document.getElementById('ra_text').value || 'JE&J', size: document.getElementById('ra_text_size').value };
      flashMessage('Click on the garment to place the text');
    });
    const imgInput = document.getElementById('ra_image_file');
    imgInput.addEventListener('change', (e)=>{
      const file = e.target.files[0];
      if (!file) return;
      const url = URL.createObjectURL(file);
      const img = new Image();
      img.onload = ()=>{
        RA3D.uploadedImage = img;
        flashMessage('Image loaded. Click "Place Image" then click the model');
      };
      img.src = url;
    });
    document.getElementById('ra_place_image').addEventListener('click', ()=>{
      if (!RA3D.uploadedImage) return flashMessage('Upload an image first');
      RA3D.placingMode = 'image';
      flashMessage('Click on the garment to place the image');
    });
    document.getElementById('ra_clear').addEventListener('click', ()=>{
      dctx.clearRect(0,0,canvasSize,canvasSize);
      decalTexture.needsUpdate = true;
      flashMessage('Prints cleared');
    });
    document.getElementById('ra_export').addEventListener('click', ()=>{
      const url = RA3D.exportCombinedTexture();
      const a = document.createElement('a');
      a.href = url;
      a.download = 'print_texture.png';
      a.click();
    });
  }

  function setBaseColor(hex){
    if (!baseMaterial) return;
    baseMaterial.color = new THREE.Color(hex);
    baseMaterial.needsUpdate = true;
  }

  function flashMessage(msg){
    // simple toast
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.position = 'fixed';
    t.style.left = '50%';
    t.style.bottom = '24px';
    t.style.transform = 'translateX(-50%)';
    t.style.background = 'rgba(0,0,0,0.8)';
    t.style.color = '#fff';
    t.style.padding = '10px 14px';
    t.style.borderRadius = '6px';
    t.style.zIndex = 99999;
    document.body.appendChild(t);
    setTimeout(()=>{ t.style.transition='opacity 300ms'; t.style.opacity='0'; }, 1400);
    setTimeout(()=> t.remove(), 1800);
  }

  // expose some functions
  RA3D.setBaseColor = setBaseColor;
  RA3D.clearPrints = ()=>{ dctx.clearRect(0,0,canvasSize,canvasSize); decalTexture.needsUpdate=true; };
  RA3D.exportTexture = ()=> decalCanvas.toDataURL('image/png');

  // Export a combined image that includes base color + decals (useful for printing)
  RA3D.exportCombinedTexture = ()=>{
    const out = document.createElement('canvas');
    out.width = canvasSize;
    out.height = canvasSize;
    const octx = out.getContext('2d');
    // determine base color
    let baseHex = '#ffffff';
    try{
      if (baseMaterial && baseMaterial.color && baseMaterial.color.getHexString){
        baseHex = '#' + baseMaterial.color.getHexString();
      }
    }catch(e){ /* ignore */ }
    octx.fillStyle = baseHex;
    octx.fillRect(0,0,out.width,out.height);
    // draw decals on top
    octx.drawImage(decalCanvas, 0, 0);
    return out.toDataURL('image/png');
  };

})();
