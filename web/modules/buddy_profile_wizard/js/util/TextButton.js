class TextButton extends Phaser.GameObjects.Text {
  constructor(scene, x, y, text, callback,style) {
    if(!style){
      style =  {
        fontFamily: 'Arial Black',
        fontSize: 50,
        color: "#000000",
        fontStyle: "bold",
        stroke: '#000000',
        strokeThickness : 6,
        fill:  '#ffffff',
      };
    }
    super(scene, x, y, text, style);


    this.setInteractive({ useHandCursor: true })
      .on('pointerover', () => this.enterButtonHoverState() )
      .on('pointerout', () => this.enterButtonRestState() )
      .on('pointerdown', () => this.enterButtonActiveState() )
      .on('pointerup', () => {
        this.enterButtonHoverState();
        callback();
      });

  }

  enterButtonHoverState() {
    this.setStyle({ fill: '#ff0'});
  }

  enterButtonRestState() {
    this.setStyle({ fill: '#0f0'});
  }

  enterButtonActiveState() {
    this.setStyle({ fill: '#0ff' });
  }
}

class MyButton extends Phaser.GameObjects.Container{
  constructor(scene,text,x,y,callback,style) {
    super(scene,x,y);

    this.callback = callback;
    this.outline = 6;

    if(!style){
      style =  {
        fontFamily: 'Arial Black',
        fontSize: 20,

        fontStyle: "bold",
        stroke: '#000000',
        strokeThickness : 6,
        fill:  '#ffffff',
      };
    }


    this.text = scene.add.text(0,0 , text, style).setOrigin(0.5);;


    this.background = scene.add.rectangle(this.text.x, this.text.y, this.text.width+this.outline, this.text.height+this.outline, 0xffffff);

    this.background.setInteractive({ useHandCursor: true })
      .on('pointerover', () => this.enterButtonHoverState() )
      .on('pointerout', () => this.enterButtonRestState() )
      .on('pointerdown', () => this.enterButtonActiveState() )
      .on('pointerup', () => {
        this.enterButtonHoverState();
        this.callback();
      });

    this.foreground = scene.add.rectangle(this.text.x, this.text.y, this.text.width, this.text.height, 0x123123);




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
