class TextButton extends Phaser.GameObjects.Container {
  constructor(scene, text, x, y, callback, labelStyle) {
    super(scene, x, y);

    this.scene = scene;
    this.text = text;
    this.callback = callback;
    this.textOutline = 20;
    this.backgroundStrokeWidth = 6;


    if (!labelStyle) {
      this.labelStyle = {
        fontFamily: 'Arial Black',
        fontSize: 20,
        align:'center',
        fontStyle: "bold",
        stroke: '#000000',
        strokeThickness: 6,
        fill: '#ffffff',
      };
    } else {
      this.labelStyle = labelStyle;
    }
  }

  init() {
    this.initContent();
    this.initBackground();
    this.createButton();
  }


  initContent() {

    this.text = this.scene.add.text(0, 0, this.text, this.labelStyle).setOrigin(0.5);

    this.backgroundWidth = this.text.width+this.textOutline;
    this.backgroundHeight = this.text.height+this.textOutline;
  }

  initBackground() {

    this.background = this.scene.add.rectangle(0, 0, this.backgroundWidth + this.backgroundStrokeWidth, this.backgroundHeight + this.backgroundStrokeWidth, 0xffffff);

    this.background.setInteractive({useHandCursor: true})
      .on('pointerover', () => this.enterButtonHoverState())
      .on('pointerout', () => this.enterButtonRestState())
      .on('pointerdown', () => this.enterButtonActiveState())
      .on('pointerup', () => {
        this.enterButtonHoverState();
        this.callback();
      });

    this.foreground = this.scene.add.rectangle(0,0, this.backgroundWidth, this.backgroundHeight, 0x123123);

  }

  createButton() {
    this.add(this.background);
    this.add(this.foreground);
    this.add(this.text);
  }

  enterButtonHoverState() {
    this.background.setFillStyle('#ff0');
  }

  enterButtonRestState() {
    this.background.setFillStyle(0xffffff);
  }

  enterButtonActiveState() {
    this.background.setFillStyle('#0ff');
  }


}


class IconButton extends TextButton {
  constructor(scene, text, x, y, icon, callback, style) {
    super(scene, text, x, y, callback, style);
    this.icon = icon;

    console.log("NEW BUTTON!!!");

  }


  initContent() {

    this.text = this.scene.add.text(0, 0, this.text, this.labelStyle).setOrigin(0,0.5);

    this.icon = this.scene.add.sprite(0, 0, this.icon).setOrigin(0,0.5);
    this.icon.setScale(this.text.height/this.icon.height);

    let totalWidth = this.text.width + this.icon.width*this.icon.scale;
    console.log(totalWidth,this.text.width,this.icon.width );

    this.icon.x = -totalWidth/2;
    this.text.x = -totalWidth/2+this.icon.width*this.icon.scale;
    this.backgroundWidth = totalWidth+this.textOutline;
    this.backgroundHeight = this.text.height+this.textOutline;
  }


  createButton() {
    this.add(this.background);
    this.add(this.foreground);
    this.add(this.text);
    this.add(this.icon);
  }
}

class TextToSpeechButton extends IconButton {
  constructor(scene, text, x, y,speech , callback, style) {
    super(scene, text, x, y, "textToSpeech",callback, style);
    this.speech = speech;

  }


  initContent() {

   super.initContent();
  }


  createButton() {
    this.add(this.background);
    this.add(this.foreground);
    this.add(this.text);
    this.add(this.icon);

    let postFxPlugin = this.scene.plugins.get('rexinversepipelineplugin');

    this.pipeline = postFxPlugin.add(this.icon, { intensity: 0 })
    this.icon.setInteractive({useHandCursor: true})
      .on('pointerover', () => this.TTSButtonHoverState())
      .on('pointerout', () => this.TTSButtonRestState())
      .on('pointerdown', () => this.TTSButtonActiveState())
      .on('pointerup', () => this.TTSButtonClicked());
  }

  TTSButtonHoverState(){

    this.pipeline.intensity =0.5;
  }

  TTSButtonRestState(){
    this.pipeline.intensity = 0;
  }

  TTSButtonActiveState(){
    this.pipeline.intensity =1.0
  }

  TTSButtonClicked(){

    this.speech.play();
    console.log("AAASDF");
  }
}

class AvatarAudioButton extends Phaser.GameObjects.Container {
  constructor(scene, audio, x, y, callback, autoplay = true) {
    super(scene, x, y);
    this.scene = scene;
    this.audio = audio;
    this.callback = callback;
    this.autoplay = autoplay;
    this.playing = false;
    this.avatarMouthOpen = false;
  }

  init() {
    this.initContent();
    this.initBackground();
    this.initSound();
    this.createButton();
  }




  initContent() {
    this.avatarSoundIcon = this.scene.add.sprite(0, 0, "avatarNormalMouthOpen");
    this.avatarSoundIcon.setScale(0.25);
    this.componentWidth = this.avatarSoundIcon.scale*this.avatarSoundIcon.width;
  }

  initBackground() {

    this.background = this.scene.add.circle(0, 0, this.componentWidth*0.7, 0xffffff);


    this.background.setInteractive({useHandCursor: true})
      .on('pointerover', () => this.enterButtonHoverState())
      .on('pointerout', () => this.enterButtonRestState())
      .on('pointerdown', () => this.enterButtonActiveState())
      .on('pointerup', () => {
        this.enterButtonHoverState();
        if(!this.playing){
          this.playSound();
          this.callback();
        }

      });


  }

  createButton() {
    this.add(this.background);
    this.add(this.avatarSoundIcon);

  }

  initSound(){
    this.sound = this.scene.sound.add(this.audio);
    let avatarAudioButton = this;
    this.sound.on('complete', function (sound) {
      avatarAudioButton.stopAnimation();
      avatarAudioButton.playing = false;
    },this);
    if(this.autoplay){

      this.playSound();

    }

  }

  playSound(){

    if(!this.playing){
      this.startAnimation();
      this.playing = true;
      this.sound.play();
    }
  }


  destroy(){
    if(this.playing){
      this.sound.removeAllListeners();
      this.sound.stop();
      this.stopAnimation();
    }
    super.destroy();
  }

  startAnimation(){


    if(this.playing){
      return;
    }


    this.background.setStrokeStyle(4, 0xefc53f);


    this.backgroundTween = this.scene.tweens.add({

      targets: this.background,
      scaleX: 0.85,
      scaleY: 0.85,
      yoyo: true,
      repeat: -1,
      duration: 200,
      ease: 'Sine.easeInOut'

    });



    this.continueAnimation();

  }

  continueAnimation(){

    let avatarAudioButton = this;
    this.timeOut = setTimeout(function(){

      if(avatarAudioButton.avatarMouthOpen){
        avatarAudioButton.avatarSoundIcon.setTexture("avatarNormal");
      }else{
        avatarAudioButton.avatarSoundIcon.setTexture("avatarNormalMouthOpen");

      }
      avatarAudioButton.avatarMouthOpen = !avatarAudioButton.avatarMouthOpen;
      avatarAudioButton.continueAnimation();
    }, Math.floor(Math.random() * 70)+70);
  }

  stopAnimation(){

    if(this.timeOut){
      clearTimeout(this.timeOut);
      this.timeOut = null;
      this.avatarSoundIcon.setTexture("avatarNormal");
    }

    if(this.backgroundTween){
      this.backgroundTween.stop();
      this.backgroundTween = null;
    }
    this.background.setStrokeStyle(0, 0xefc53f);

  }

  enterButtonHoverState() {
    this.background.setFillStyle('#ff0');
  }

  enterButtonRestState() {
    this.background.setFillStyle(0xffffff);
  }

  enterButtonActiveState() {
    this.background.setFillStyle('#0ff');
  }


}
