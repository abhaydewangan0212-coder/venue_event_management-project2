// a java program to demonstrate constructor overloading 

class Box{
    double width, height, depth;
    Box(double w, double h, double d)
    {
        width = w;
        height = h;
        depth = d;
    }

    Box() { width = height = depth = 0 ;}
    Box(double len) { width = height = depth = len; }

    double volume()
    {
        return width * height * depth;
    }
}

public class Constructor {

    public static void main(String[] args)
    {
        Box mybox1 = new Box(10, 20, 30);
        Box mybox2 = new Box();
        Box mycube = new Box(3);

        double vol;

        vol = mybox1.volume();

        System.out.println("The Volume of mybox2 is: " +vol);

        vol = mybox2.volume();

        System.out.println("The Volume of mybox2 is: " +vol);

        vol = mycube.volume();

        System.out.println("The Volume of cube is : " +vol);
    }
}